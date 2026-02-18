<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Examination;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\FacultySubject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Exception;

class SchedulerService
{
    private $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    // Map day names to numbers
    private $dayNameToNumber = [
        'Monday' => 1,
        'Tuesday' => 2,
        'Wednesday' => 3,
        'Thursday' => 4,
        'Friday' => 5,
        'Saturday' => 6,
        'Sunday' => 7
    ];
    
    // FIXED: Time slots from 7 AM to 7 PM (30-minute intervals)
    // These represent the START times of each 30-minute slot
    private $timeSlots = [
        ['start' => '07:00:00', 'end' => '07:30:00'],
        ['start' => '07:30:00', 'end' => '08:00:00'],
        ['start' => '08:00:00', 'end' => '08:30:00'],
        ['start' => '08:30:00', 'end' => '09:00:00'],
        ['start' => '09:00:00', 'end' => '09:30:00'],
        ['start' => '09:30:00', 'end' => '10:00:00'],
        ['start' => '10:00:00', 'end' => '10:30:00'],
        ['start' => '10:30:00', 'end' => '11:00:00'],
        ['start' => '11:00:00', 'end' => '11:30:00'],
        ['start' => '11:30:00', 'end' => '12:00:00'],
        ['start' => '12:00:00', 'end' => '12:30:00'],
        ['start' => '12:30:00', 'end' => '13:00:00'],
        ['start' => '13:00:00', 'end' => '13:30:00'],
        ['start' => '13:30:00', 'end' => '14:00:00'],
        ['start' => '14:00:00', 'end' => '14:30:00'],
        ['start' => '14:30:00', 'end' => '15:00:00'],
        ['start' => '15:00:00', 'end' => '15:30:00'],
        ['start' => '15:30:00', 'end' => '16:00:00'],
        ['start' => '16:00:00', 'end' => '16:30:00'],
        ['start' => '16:30:00', 'end' => '17:00:00'],
        ['start' => '17:00:00', 'end' => '17:30:00'],
        ['start' => '17:30:00', 'end' => '18:00:00'],
        ['start' => '18:00:00', 'end' => '18:30:00'],
        ['start' => '18:30:00', 'end' => '19:00:00'],
    ];

    // ============================================================================
    // UNAVAILABILITY CACHE
    // ============================================================================
    // Caches faculty_unavailabilities per request so the DB is not hit repeatedly
    // for the same faculty_code during a single schedule generation run.
    // Structure: [ faculty_code => Collection grouped by day_number ]
    // ============================================================================
    private $unavailabilityCache = [];

    // ============================================================================
    // CORE: Load and cache unavailability for a faculty member
    // This is the SINGLE source of truth for all unavailability checks.
    // Every other method MUST call this instead of querying the DB directly.
    // ============================================================================

    /**
     * Load (or return cached) unavailability records for a faculty member.
     * Returns a Collection grouped by day number.
     * On any DB error returns an EMPTY collection so scheduling is allowed
     * but the error is logged for investigation.
     *
     * @param  string $facultyCode
     * @return array<int, stdClass[]>  plain PHP array keyed by integer day number
     */
    private function loadFacultyUnavailability(string $facultyCode): array
    {
        if (array_key_exists($facultyCode, $this->unavailabilityCache)) {
            return $this->unavailabilityCache[$facultyCode];
        }

        try {
            // -----------------------------------------------------------------------
            // NORMALIZE: The `day` column in faculty_unavailabilities may be stored
            // as a day-name string ("Monday") OR as an integer/numeric-string (1/"1").
            //
            // We build a plain PHP array keyed by integer day number so that:
            //   - isFacultyUnavailable() can do a simple isset($grouped[$intKey]) lookup
            //   - foreach over $grouped[$dayNumber] always yields plain stdClass objects
            //     with ->start_time and ->end_time properties directly accessible.
            //
            // We intentionally avoid Laravel's groupBy() here because when the key
            // is an integer, groupBy wraps each group in a nested Collection and the
            // numeric key type can behave inconsistently, causing "$record->start_time"
            // to throw "Undefined property: stdClass::$start_time" when the foreach
            // iterates a Collection object instead of the raw stdClass record.
            // -----------------------------------------------------------------------
            $rows = DB::table('faculty_unavailabilities')
                ->where('faculty_code', $facultyCode)
                ->get();

            // -----------------------------------------------------------------------
            // COLUMN NAME NORMALISATION
            // The faculty_unavailabilities table may use different column names
            // depending on how the migration was written:
            //   start_time / end_time       (most common)
            //   time_from  / time_to        (alternative)
            //   start      / end            (short form)
            // We resolve whichever pair is present and always store as a plain
            // stdClass with guaranteed ->start_time and ->end_time properties
            // so every downstream caller can use those names safely.
            // -----------------------------------------------------------------------
            $grouped = [];
            foreach ($rows as $record) {
                $raw = (array) $record; // convert to array so we can inspect keys

                // --- Resolve start time ---
                if (isset($raw['start_time'])) {
                    $start = $raw['start_time'];
                } elseif (isset($raw['time_from'])) {
                    $start = $raw['time_from'];
                } elseif (isset($raw['start'])) {
                    $start = $raw['start'];
                } else {
                    Log::warning("loadFacultyUnavailability: cannot find start-time column for faculty {$facultyCode}. Available columns: " . implode(', ', array_keys($raw)));
                    continue;
                }

                // --- Resolve end time ---
                if (isset($raw['end_time'])) {
                    $end = $raw['end_time'];
                } elseif (isset($raw['time_to'])) {
                    $end = $raw['time_to'];
                } elseif (isset($raw['end'])) {
                    $end = $raw['end'];
                } else {
                    Log::warning("loadFacultyUnavailability: cannot find end-time column for faculty {$facultyCode}. Available columns: " . implode(', ', array_keys($raw)));
                    continue;
                }

                // --- Resolve day (integer or name string) ---
                $rawDay = $raw['day'] ?? null;
                if ($rawDay === null) {
                    Log::warning("loadFacultyUnavailability: missing day column for faculty {$facultyCode}");
                    continue;
                }

                if (is_numeric($rawDay)) {
                    $dayInt = (int) $rawDay;
                } else {
                    $dayInt = $this->dayNameToNumber[$rawDay] ?? null;
                    if ($dayInt === null) {
                        Log::warning("loadFacultyUnavailability: unrecognised day value '{$rawDay}' for faculty {$facultyCode} — record skipped");
                        continue;
                    }
                }

                // Build a clean normalised object with guaranteed property names
                $normalised              = new \stdClass();
                $normalised->day         = $dayInt;
                $normalised->start_time  = $start;
                $normalised->end_time    = $end;
                $normalised->faculty_code = $facultyCode;

                $grouped[$dayInt][] = $normalised;
            }

            // Store as a plain PHP array: [ int $dayNumber => stdClass[] ]
            // We deliberately do NOT wrap in a Laravel Collection because collect()
            // on an integer-keyed array can re-index keys to 0,1,2… in some Laravel
            // versions, silently destroying the day-number semantics and causing
            // isset($grouped[$dayNumber]) to always return false.
            // All callers use plain isset() / foreach on the raw array instead.
            $this->unavailabilityCache[$facultyCode] = $grouped;

            if (!empty($grouped)) {
                $summary = [];
                foreach ($grouped as $dayNum => $slots) {
                    $dayName = array_search((int) $dayNum, $this->dayNameToNumber) ?: "Day {$dayNum}";
                    foreach ($slots as $s) {
                        $summary[] = "{$dayName} {$s->start_time}–{$s->end_time}";
                    }
                }
                Log::info("🔒 [UNAVAILABILITY LOADED] Faculty {$facultyCode}", [
                    'faculty_code'  => $facultyCode,
                    'total_records' => $rows->count(),
                    'restrictions'  => $summary,
                ]);
            }

            return $grouped;

        } catch (Exception $e) {
            Log::error("CRITICAL: Failed to load unavailability for faculty {$facultyCode} — scheduling will PROCEED without restrictions. Investigate immediately.", [
                'faculty_code' => $facultyCode,
                'error'        => $e->getMessage(),
            ]);
            // Return empty collection; do NOT cache the error so next call retries.
            return []; // empty plain array — matches return type array
        }
    }

    /**
     * MANDATORY HARD GATE — call this before attempting to place ANY session
     * (lecture, laboratory, or examination) into a time slot.
     *
     * Returns TRUE  → faculty IS unavailable; the slot MUST be skipped.
     * Returns FALSE → faculty is available; proceed with other conflict checks.
     *
     * Works for both day-name strings ("Monday") and day numbers (1).
     *
     * @param  string       $facultyCode
     * @param  string|int   $day          Day name or day number
     * @param  string       $startTime    HH:MM:SS
     * @param  string       $endTime      HH:MM:SS
     * @return bool
     */
    private function isFacultyUnavailable($facultyCode, $day, string $startTime, string $endTime): bool
    {
        $dayNumber = is_numeric($day) ? (int) $day : ($this->dayNameToNumber[$day] ?? null);

        if ($dayNumber === null) {
            Log::warning("isFacultyUnavailable: unknown day value '{$day}' for faculty {$facultyCode} — treating as AVAILABLE");
            return false;
        }

        $unavailabilities = $this->loadFacultyUnavailability($facultyCode);

        // $unavailabilities is a plain PHP array: [ int $dayNumber => stdClass[] ]
        if (!isset($unavailabilities[$dayNumber])) {
            return false;
        }

        foreach ($unavailabilities[$dayNumber] as $record) {
            if ($this->timesOverlap($startTime, $endTime, $record->start_time, $record->end_time)) {
                Log::debug("🚫 [UNAVAILABILITY BLOCK] Faculty {$facultyCode} blocked", [
                    'faculty_code'     => $facultyCode,
                    'day'              => $day,
                    'requested_time'   => "{$startTime}–{$endTime}",
                    'blocked_by_rule'  => "{$record->start_time}–{$record->end_time}",
                ]);
                return true; // Hard block
            }
        }

        return false;
    }

    /**
     * Pre-filter: given a day name, return only the continuous time slots for
     * `$hours` hours that do NOT overlap any unavailability for the faculty on
     * that day.  This eliminates unavailable slots before the classroom loop
     * even begins, making scheduling more efficient.
     *
     * @param  string $facultyCode
     * @param  string $dayName     e.g. "Monday"
     * @param  int    $hours
     * @return array  Array of ['start' => ..., 'end' => ...] that are free
     */
    private function getAvailableTimeSlots(string $facultyCode, string $dayName, int $hours): array
    {
        $all = $this->getContinuousTimeSlots($hours);

        $allowed = [];
        foreach ($all as $slot) {
            if (!$this->isFacultyUnavailable($facultyCode, $dayName, $slot['start'], $slot['end'])) {
                $allowed[] = $slot;
            }
        }

        return $allowed;
    }

    /**
     * Returns TRUE if the faculty has at least one fully-blocked day
     * (the unavailability range covers the entire operating window 07:00–19:00).
     * Used only for logging / conflict reporting.
     */
    private function isDayFullyBlocked(string $facultyCode, string $dayName): bool
    {
        return $this->isFacultyUnavailable($facultyCode, $dayName, '07:00:00', '19:00:00');
    }

    // ============================================================================
    // EXAMINATION GENERATION
    // ============================================================================

    /**
     * Generate examination preview separately from class schedules.
     * Fully respects faculty_unavailabilities for all exam slots.
     */
    public function generateExaminationPreview()
    {
        try {
            Log::info('=== EXAMINATION GENERATION START ===');
            $this->unavailabilityCache = []; // Reset cache for each generation run

            $facultyAssignments = DB::table('faculty_subject')
                ->join('users', 'faculty_subject.faculty_code', '=', 'users.faculty_code')
                ->join('subjects', 'faculty_subject.subject_id', '=', 'subjects.id')
                ->select(
                    'faculty_subject.id as assignment_id',
                    'faculty_subject.faculty_code',
                    'faculty_subject.subject_id',
                    'faculty_subject.lecture_units',
                    'faculty_subject.laboratory_units',
                    'users.id as faculty_id',
                    'users.name as faculty_name',
                    'subjects.subject_name',
                    'subjects.course_code',
                    'subjects.year_level',
                    'subjects.semester',
                    DB::raw('(COALESCE(faculty_subject.lecture_units, 0) + COALESCE(faculty_subject.laboratory_units, 0)) as total_units')
                )
                ->havingRaw('total_units > 0')
                ->get();

            if ($facultyAssignments->isEmpty()) {
                return ['success' => false, 'message' => 'No faculty-subject assignments found.', 'examinations' => [], 'conflicts' => []];
            }

            $classrooms = Classroom::all();
            if ($classrooms->isEmpty()) {
                return ['success' => false, 'message' => 'No classrooms found.', 'examinations' => [], 'conflicts' => []];
            }

            $examinations = [];
            $conflicts    = [];

            foreach ($facultyAssignments as $assignment) {
                $totalUnits = (float)($assignment->lecture_units ?? 0) + (float)($assignment->laboratory_units ?? 0);
                if ($totalUnits < 1) continue;

                $exam = $this->generateExaminationForAssignment($assignment, $classrooms, $examinations, $totalUnits);

                if ($exam) {
                    $examinations[] = $exam;
                } else {
                    $unavailabilitySlots  = $this->getFacultyUnavailabilitySlots($assignment->faculty_code);
                    $slotDescriptions     = array_map(fn($slot) => $slot['formatted'], $unavailabilitySlots);
                    $unavailabilityDetails = !empty($slotDescriptions)
                        ? ' ⚠️ Faculty unavailable: ' . implode(', ', $slotDescriptions)
                        : '';

                    $conflicts[] = [
                        'assignment_id'      => $assignment->assignment_id,
                        'faculty_code'       => $assignment->faculty_code,
                        'faculty'            => $assignment->faculty_name,
                        'subject'            => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'reason'             => 'Could not find available examination slot' . $unavailabilityDetails,
                        'unavailability_count' => count($unavailabilitySlots),
                        'unavailable_periods' => $slotDescriptions,
                    ];
                }
            }

            $examinations = array_map(function ($exam) {
                $exam['start_time'] = substr($exam['start_time'], 0, 5);
                $exam['end_time']   = substr($exam['end_time'], 0, 5);
                return $exam;
            }, $examinations);

            Log::info('=== EXAMINATION GENERATION COMPLETE ===', [
                'exams'     => count($examinations),
                'conflicts' => count($conflicts),
            ]);

            return [
                'success'      => true,
                'examinations' => $examinations,
                'conflicts'    => $conflicts,
                'message'      => count($examinations) . ' examinations generated successfully',
                'stats'        => [
                    'total_exams'      => count($examinations),
                    'total_conflicts'  => count($conflicts),
                ],
            ];

        } catch (Exception $e) {
            Log::error('SchedulerService generateExaminationPreview error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Error generating examinations: ' . $e->getMessage(), 'examinations' => [], 'conflicts' => []];
        }
    }

    // ============================================================================
    // SCHEDULE GENERATION
    // ============================================================================

    /**
     * Generate schedule preview (regular class schedules only).
     * Faculty unavailability is enforced as a mandatory hard gate for BOTH
     * lecture and laboratory sessions via isFacultyUnavailable().
     */
    public function generateSchedulePreview()
    {
        try {
            Log::info('=== SCHEDULE GENERATION START ===');
            $this->unavailabilityCache = []; // Reset cache for each generation run

            $facultyAssignments = DB::table('faculty_subject')
                ->join('users', 'faculty_subject.faculty_code', '=', 'users.faculty_code')
                ->join('subjects', 'faculty_subject.subject_id', '=', 'subjects.id')
                ->select(
                    'faculty_subject.id as assignment_id',
                    'faculty_subject.faculty_code',
                    'faculty_subject.subject_id',
                    'faculty_subject.lecture_units',
                    'faculty_subject.laboratory_units',
                    'users.id as faculty_id',
                    'users.name as faculty_name',
                    'subjects.subject_name',
                    'subjects.course_code',
                    'subjects.year_level',
                    'subjects.semester',
                    DB::raw('(COALESCE(faculty_subject.lecture_units, 0) + COALESCE(faculty_subject.laboratory_units, 0)) as total_units')
                )
                ->havingRaw('total_units > 0')
                ->get();

            Log::info('Faculty Assignments Query Result', [
                'count'       => $facultyAssignments->count(),
                'sample_data' => $facultyAssignments->take(2)->toArray(),
            ]);

            // Log unavailability summary for all faculty who have restrictions
            $this->logUnavailabilitySummary();

            if ($facultyAssignments->isEmpty()) {
                return [
                    'success'      => false,
                    'message'      => 'No faculty-subject assignments found. Please assign subjects to faculty first.',
                    'schedules'    => [],
                    'examinations' => [],
                    'conflicts'    => [],
                ];
            }

            $classrooms = Classroom::all();
            if ($classrooms->isEmpty()) {
                return [
                    'success'      => false,
                    'message'      => 'No classrooms found. Add classrooms first.',
                    'schedules'    => [],
                    'examinations' => [],
                    'conflicts'    => [],
                ];
            }

            $lectureRooms = $classrooms->filter(fn($room) =>
                in_array(strtolower($room->room_type ?? ''), ['classroom', 'lecture', 'lecture room']) ||
                stripos($room->room_name ?? '', 'lab') === false
            );

            $labRooms = $classrooms->filter(fn($room) =>
                in_array(strtolower($room->room_type ?? ''), ['laboratory', 'lab', 'computer lab']) ||
                stripos($room->room_name ?? '', 'lab') !== false
            );

            if ($lectureRooms->isEmpty()) $lectureRooms = $classrooms;
            if ($labRooms->isEmpty())     $labRooms     = $classrooms;

            $schedules       = [];
            $conflicts       = [];
            $subjectDayUsage = [];

            foreach ($facultyAssignments as $assignment) {
                $lectureUnits = (float)($assignment->lecture_units ?? 0);
                $labUnits     = (float)($assignment->laboratory_units ?? 0);
                $totalUnits   = $lectureUnits + $labUnits;

                if ($totalUnits < 1) continue;

                // Pre-warm unavailability cache for this faculty member
                $this->loadFacultyUnavailability($assignment->faculty_code);

                $distribution = $this->getClassDistributionFromFacultySubject($lectureUnits, $labUnits);

                $scheduled   = false;
                $attemptCount = 0;
                $maxAttempts  = 100;

                while (!$scheduled && $attemptCount < $maxAttempts) {
                    $attemptCount++;

                    $sessionSchedules = $this->scheduleAssignmentSessions(
                        $assignment,
                        $distribution,
                        $lectureRooms,
                        $labRooms,
                        $schedules,
                        $subjectDayUsage
                    );

                    if ($sessionSchedules !== false) {
                        $schedules = array_merge($schedules, $sessionSchedules);
                        $scheduled = true;

                        $subjectKey = $assignment->year_level . '_' . $assignment->subject_id;
                        foreach ($sessionSchedules as $session) {
                            if (!isset($subjectDayUsage[$subjectKey])) {
                                $subjectDayUsage[$subjectKey] = [];
                            }
                            $subjectDayUsage[$subjectKey][] = $session['day_name'];
                        }
                    }
                }

                if (!$scheduled) {
                    $unavailabilitySlots  = $this->getFacultyUnavailabilitySlots($assignment->faculty_code);
                    $slotDescriptions     = array_map(fn($slot) => $slot['formatted'], $unavailabilitySlots);
                    $unavailabilityDetails = !empty($slotDescriptions)
                        ? ' ⚠️ Faculty unavailable: ' . implode(', ', $slotDescriptions)
                        : '';

                    $conflicts[] = [
                        'assignment_id'      => $assignment->assignment_id,
                        'faculty_code'       => $assignment->faculty_code,
                        'faculty'            => $assignment->faculty_name,
                        'subject'            => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'lecture_units'      => $lectureUnits,
                        'laboratory_units'   => $labUnits,
                        'total_units'        => $totalUnits,
                        'reason'             => 'Could not find available time slots after ' . $maxAttempts . ' attempts.' . $unavailabilityDetails,
                        'unavailability_count' => count($unavailabilitySlots),
                        'unavailable_periods' => $slotDescriptions,
                    ];

                    Log::warning("❌ CONFLICT: Unable to schedule", [
                        'faculty'              => $assignment->faculty_name,
                        'faculty_code'         => $assignment->faculty_code,
                        'subject'              => $assignment->subject_name,
                        'attempts'             => $maxAttempts,
                        'unavailable_slots'    => count($unavailabilitySlots),
                        'unavailability_details' => $slotDescriptions,
                    ]);
                }
            }

            $formatTime = fn($s) => array_merge($s, [
                'start_time' => substr($s['start_time'], 0, 5),
                'end_time'   => substr($s['end_time'], 0, 5),
            ]);

            $schedules = array_map($formatTime, $schedules);

            Log::info('=== SCHEDULE GENERATION COMPLETE ===', [
                'schedules' => count($schedules),
                'conflicts' => count($conflicts),
            ]);

            return [
                'success'      => true,
                'schedules'    => $schedules,
                'examinations' => [], // Examinations generated separately
                'conflicts'    => $conflicts,
                'message'      => count($schedules) . ' schedule sessions generated successfully',
                'stats'        => [
                    'total_schedules'  => count($schedules),
                    'total_exams'      => 0,
                    'total_conflicts'  => count($conflicts),
                    'faculty_count'    => count($facultyAssignments),
                ],
            ];

        } catch (Exception $e) {
            Log::error('SchedulerService generateSchedulePreview error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Error generating schedule: ' . $e->getMessage(), 'schedules' => [], 'examinations' => [], 'conflicts' => []];
        }
    }

    // ============================================================================
    // DISTRIBUTION HELPERS
    // ============================================================================

    private function getClassDistributionFromFacultySubject($lectureUnits, $labUnits): array
    {
        $distribution = [];

        if ($lectureUnits > 0) {
            $distribution[] = ['type' => 'Lecture', 'hours' => (int)$lectureUnits];
        }

        if ($labUnits > 0) {
            $distribution[] = ['type' => 'Laboratory', 'hours' => (int)($labUnits * 3)];
        }

        return $distribution;
    }

    /** @deprecated Keep for backward compatibility only */
    private function getClassDistribution($units): array
    {
        $units = (int)$units;
        switch ($units) {
            case 2:  return [['type' => 'Lecture', 'hours' => 2]];
            case 3:  return [['type' => 'Lecture', 'hours' => 2], ['type' => 'Laboratory', 'hours' => 3]];
            case 4:  return [['type' => 'Lecture', 'hours' => 2], ['type' => 'Laboratory', 'hours' => 6]];
            case 5:  return [['type' => 'Lecture', 'hours' => 5]];
            case 6:  return [['type' => 'Lecture', 'hours' => 6]];
            default: return [['type' => 'Lecture', 'hours' => $units]];
        }
    }

    // ============================================================================
    // SLOT FINDING — APPLIES TO BOTH LECTURE AND LABORATORY
    // ============================================================================

    private function scheduleAssignmentSessions($assignment, $distribution, $lectureRooms, $labRooms, $existingSchedules, $subjectDayUsage)
    {
        $sessionSchedules = [];
        $usedDays         = [];

        $subjectKey   = $assignment->year_level . '_' . $assignment->subject_id;
        $existingDays = $subjectDayUsage[$subjectKey] ?? [];

        foreach ($distribution as $session) {
            $hours     = $session['hours'];
            $classType = $session['type'];
            $rooms     = $classType === 'Laboratory' ? $labRooms : $lectureRooms;

            if ($rooms->isEmpty()) return false;

            $slot = $this->findAvailableSlotForAssignment(
                $assignment,
                $hours,
                $classType,
                $rooms,
                array_merge($existingSchedules, $sessionSchedules),
                array_merge($usedDays, $existingDays)
            );

            if (!$slot) return false;

            $sessionSchedules[] = $slot;
            $usedDays[]         = $slot['day_name'];
        }

        return $sessionSchedules;
    }

    /**
     * Find an available time slot for an assignment session (lecture OR laboratory).
     *
     * MANDATORY UNAVAILABILITY ENFORCEMENT STRATEGY:
     * ─────────────────────────────────────────────
     * 1. Load faculty unavailability via the cache (loadFacultyUnavailability).
     * 2. For each day, call getAvailableTimeSlots() which pre-filters out ALL
     *    slots that overlap any unavailability rule — this is O(slots) not O(slots×rules).
     * 3. Days where NO time slot survives the filter are skipped entirely before
     *    any classroom check is attempted.
     * 4. isSlotAvailableForAssignment() adds a second gate (isFacultyUnavailable)
     *    as a final safety net in case the caller bypasses this method.
     *
     * This applies identically to both Lecture and Laboratory sessions.
     */
    private function findAvailableSlotForAssignment($assignment, $hours, $classType, $classrooms, $existingSchedules, $usedDays = [])
    {
        // ── STEP 1: Ensure unavailability is loaded (may be cached already) ──────
        // Returns a plain PHP array: [ int $dayNumber => stdClass[] ]
        $facultyUnavailabilities = $this->loadFacultyUnavailability($assignment->faculty_code);

        if (!empty($facultyUnavailabilities)) {
            $unavailableDays = array_map(function ($dayNum) {
                return array_search((int) $dayNum, $this->dayNameToNumber) ?: "Day {$dayNum}";
            }, array_keys($facultyUnavailabilities));

            $totalRestrictions = array_sum(array_map('count', $facultyUnavailabilities));

            Log::info("🚫 [UNAVAILABILITY ACTIVE] Faculty has restrictions — will filter slots before scheduling", [
                'faculty_code'       => $assignment->faculty_code,
                'faculty_name'       => $assignment->faculty_name,
                'subject'            => $assignment->subject_name,
                'class_type'         => $classType,
                'hours'              => $hours,
                'unavailable_days'   => $unavailableDays,
                'total_restrictions' => $totalRestrictions,
            ]);
        }

        $shuffledDays      = $this->daysOfWeek;
        shuffle($shuffledDays);
        $shuffledClassrooms = $classrooms->shuffle();

        // ── STEP 2: Iterate days ──────────────────────────────────────────────────
        foreach ($shuffledDays as $day) {

            // Skip day already used for this subject
            if (in_array($day, $usedDays)) continue;

            // ── STEP 3: Pre-filter slots — removes ANY slot that overlaps a rule ──
            // This is the MANDATORY hard gate for both lecture and laboratory.
            // getAvailableTimeSlots() internally calls isFacultyUnavailable() for
            // every candidate slot and discards blocked ones.
            $allowedSlots = $this->getAvailableTimeSlots($assignment->faculty_code, $day, $hours);

            if (empty($allowedSlots)) {
                Log::info("⛔ [DAY FULLY BLOCKED] Skipping day entirely — no valid slots after unavailability filter", [
                    'faculty_code' => $assignment->faculty_code,
                    'faculty_name' => $assignment->faculty_name,
                    'subject'      => $assignment->subject_name,
                    'class_type'   => $classType,
                    'day'          => $day,
                    'hours'        => $hours,
                ]);
                continue; // No point checking classrooms on a fully blocked day
            }

            shuffle($allowedSlots);

            // ── STEP 4: Check classrooms for each remaining (allowed) slot ─────────
            foreach ($allowedSlots as $timeSlot) {
                foreach ($shuffledClassrooms as $classroom) {
                    if ($this->isSlotAvailableForAssignment(
                        $existingSchedules, $day,
                        $timeSlot['start'], $timeSlot['end'],
                        $classroom->id, $assignment
                    )) {
                        $yearSection = $assignment->year_level . '-A';
                        $totalUnits  = (float)($assignment->lecture_units ?? 0) + (float)($assignment->laboratory_units ?? 0);

                        Log::info("✅ [SCHEDULED] Faculty available and slot confirmed", [
                            'faculty'      => $assignment->faculty_name,
                            'faculty_code' => $assignment->faculty_code,
                            'subject'      => $assignment->subject_name,
                            'class_type'   => $classType,
                            'day'          => $day,
                            'time'         => "{$timeSlot['start']}–{$timeSlot['end']}",
                            'classroom'    => $classroom->room_name ?? $classroom->name,
                            'hours'        => $hours,
                        ]);

                        return [
                            'faculty_id'       => $assignment->faculty_id,
                            'faculty_code'     => $assignment->faculty_code,
                            'subject_id'       => $assignment->subject_id,
                            'classroom_id'     => $classroom->id,
                            'day'              => $day,
                            'day_name'         => $day,
                            'start_time'       => $timeSlot['start'],
                            'end_time'         => $timeSlot['end'],
                            'schedule_date'    => $this->getNextDateForDay($day),
                            'class_type'       => $classType,
                            'faculty_name'     => $assignment->faculty_name,
                            'course_subject'   => $assignment->subject_name,
                            'course_code'      => $assignment->course_code,
                            'units'            => $totalUnits,
                            'lecture_units'    => $assignment->lecture_units ?? 0,
                            'laboratory_units' => $assignment->laboratory_units ?? 0,
                            'year_section'     => $yearSection,
                            'classroom_name'   => $classroom->room_name ?? $classroom->name ?? 'Room ' . $classroom->id,
                            'year_level'       => $assignment->year_level,
                            'hours'            => $hours,
                            'semester'         => $assignment->semester,
                        ];
                    }
                }
            }
        }

        // ── NO SLOT FOUND ─────────────────────────────────────────────────────────
        $unavailabilityDetails = $this->getFacultyUnavailabilitySummary($assignment->faculty_code);

        Log::warning("❌ [SCHEDULING FAILED] No available slots found", [
            'faculty'             => $assignment->faculty_name,
            'faculty_code'        => $assignment->faculty_code,
            'subject'             => $assignment->subject_name,
            'class_type'          => $classType,
            'hours_needed'        => $hours,
            'unavailability'      => $unavailabilityDetails,
            'has_restrictions'    => !empty($facultyUnavailabilities),
        ]);

        return false;
    }

    /**
     * FIXED: Get continuous time slots for a given number of hours.
     * 1 hour = 2 × 30-minute slots.
     */
    private function getContinuousTimeSlots($hours): array
    {
        $continuousSlots = [];
        $slotsNeeded     = $hours * 2;

        for ($i = 0; $i <= count($this->timeSlots) - $slotsNeeded; $i++) {
            $startTime    = $this->timeSlots[$i]['start'];
            $endSlotIndex = $i + $slotsNeeded - 1;
            $endTime      = $this->timeSlots[$endSlotIndex]['end'];

            $continuousSlots[] = ['start' => $startTime, 'end' => $endTime];
        }

        return $continuousSlots;
    }

    // ============================================================================
    // SLOT AVAILABILITY (includes second-gate unavailability check)
    // ============================================================================

    /**
     * Check that a time slot is free of ALL conflict types:
     *
     *   1. Faculty Unavailability  — MANDATORY hard block (second gate)
     *   2. Classroom Conflict      — room already in use
     *   3. Faculty Double-booking  — faculty already teaching
     *   4. Section Conflict        — section already has a class
     *
     * The unavailability check here acts as a SAFETY NET.  The primary filter
     * is in findAvailableSlotForAssignment → getAvailableTimeSlots.  Both gates
     * must pass independently; removing either would be a bug.
     */
    private function isSlotAvailableForAssignment($schedules, $day, $startTime, $endTime, $classroomId, $assignment): bool
    {
        $assignmentSection = $assignment->year_level . '-A';

        // ── GATE 1: Faculty Unavailability (second/safety-net check) ─────────────
        if ($this->isFacultyUnavailable($assignment->faculty_code, $day, $startTime, $endTime)) {
            Log::debug("❌ [UNAVAILABILITY BLOCK — SAFETY NET] Faculty blocked at second gate", [
                'faculty_code' => $assignment->faculty_code,
                'faculty_name' => $assignment->faculty_name,
                'day'          => $day,
                'time'         => "{$startTime}–{$endTime}",
            ]);
            return false;
        }

        // ── GATES 2–4: Iterate existing schedules ────────────────────────────────
        foreach ($schedules as $schedule) {
            $scheduleDay = $schedule['day_name'] ?? $schedule['day'];

            if ($scheduleDay !== $day) continue;
            if (!$this->timesOverlap($startTime, $endTime, $schedule['start_time'], $schedule['end_time'])) continue;

            // Gate 2: Classroom conflict
            if ($schedule['classroom_id'] == $classroomId) {
                Log::warning("❌ [CLASSROOM CONFLICT]", [
                    'classroom_id'      => $classroomId,
                    'day'               => $day,
                    'requested_subject' => $assignment->subject_name,
                    'requested_faculty' => $assignment->faculty_name,
                    'requested_time'    => "{$startTime}–{$endTime}",
                    'existing_subject'  => $schedule['course_subject'] ?? 'N/A',
                    'existing_time'     => "{$schedule['start_time']}–{$schedule['end_time']}",
                ]);
                return false;
            }

            // Gate 3: Faculty double-booking
            if (isset($schedule['faculty_code']) && $schedule['faculty_code'] == $assignment->faculty_code) {
                Log::debug("❌ [FACULTY DOUBLE-BOOKING]", [
                    'faculty_code'      => $assignment->faculty_code,
                    'day'               => $day,
                    'requested_subject' => $assignment->subject_name,
                    'requested_time'    => "{$startTime}–{$endTime}",
                    'existing_subject'  => $schedule['course_subject'] ?? 'N/A',
                    'existing_time'     => "{$schedule['start_time']}–{$schedule['end_time']}",
                ]);
                return false;
            }

            // Gate 4: Section conflict
            if (($schedule['year_section'] ?? null) === $assignmentSection) {
                Log::debug("❌ [SECTION CONFLICT]", [
                    'section'           => $assignmentSection,
                    'day'               => $day,
                    'requested_subject' => $assignment->subject_name,
                    'requested_time'    => "{$startTime}–{$endTime}",
                    'existing_subject'  => $schedule['course_subject'] ?? 'N/A',
                    'existing_time'     => "{$schedule['start_time']}–{$schedule['end_time']}",
                ]);
                return false;
            }
        }

        Log::debug("✅ [SLOT AVAILABLE] All gates passed", [
            'subject'   => $assignment->subject_name,
            'faculty'   => $assignment->faculty_name,
            'classroom' => $classroomId,
            'day'       => $day,
            'time'      => "{$startTime}–{$endTime}",
        ]);

        return true;
    }

    // ============================================================================
    // EXAMINATION SLOT GENERATION
    // ============================================================================

    /**
     * Generate a single examination for an assignment.
     * Faculty unavailability is enforced via the same isFacultyUnavailable gate
     * used for regular sessions — no special-casing.
     */
    private function generateExaminationForAssignment($assignment, $classrooms, $existingExams, $totalUnits)
    {
        // Ensure unavailability is loaded — returns plain PHP array: [ int => stdClass[] ]
        $facultyUnavailabilities = $this->loadFacultyUnavailability($assignment->faculty_code);

        if (!empty($facultyUnavailabilities)) {
            $totalRestrictions = array_sum(array_map('count', $facultyUnavailabilities));
            Log::info("🚫 [UNAVAILABILITY ACTIVE — EXAM] Restrictions loaded for exam scheduling", [
                'faculty_code'       => $assignment->faculty_code,
                'faculty_name'       => $assignment->faculty_name,
                'subject'            => $assignment->subject_name,
                'total_restrictions' => $totalRestrictions,
            ]);
        }

        $weeksAhead = rand(8, 10);

        // All examination slots are 1 hour
        $examTimeSlots = [
            ['start' => '08:00:00', 'end' => '09:00:00'],
            ['start' => '09:00:00', 'end' => '10:00:00'],
            ['start' => '10:00:00', 'end' => '11:00:00'],
            ['start' => '11:00:00', 'end' => '12:00:00'],
            ['start' => '13:00:00', 'end' => '14:00:00'],
            ['start' => '14:00:00', 'end' => '15:00:00'],
            ['start' => '15:00:00', 'end' => '16:00:00'],
            ['start' => '16:00:00', 'end' => '17:00:00'],
        ];

        $shuffledDays = $this->daysOfWeek;
        shuffle($shuffledDays);

        foreach ($shuffledDays as $day) {

            // ── Pre-filter exam slots for this day ────────────────────────────────
            // Remove any slot that overlaps an unavailability rule on this day.
            // This is the MANDATORY hard gate for examination scheduling.
            $allowedSlots = array_filter($examTimeSlots, fn($slot) =>
                !$this->isFacultyUnavailable($assignment->faculty_code, $day, $slot['start'], $slot['end'])
            );

            if (empty($allowedSlots)) {
                Log::info("⛔ [EXAM DAY FULLY BLOCKED] Skipping day — all exam slots blocked by unavailability", [
                    'faculty_code' => $assignment->faculty_code,
                    'subject'      => $assignment->subject_name,
                    'day'          => $day,
                ]);
                continue;
            }

            shuffle($allowedSlots);

            foreach ($allowedSlots as $slot) {
                foreach ($classrooms->shuffle() as $classroom) {
                    $specificExamDate = $this->getDateForDayInFuture($day, $weeksAhead);

                    if ($this->isExamSlotAvailableForAssignment($existingExams, $specificExamDate, $slot, $classroom->id, $assignment)) {
                        $yearSection = $assignment->year_level . '-A';

                        Log::info("✅ [EXAM SCHEDULED] Faculty available and exam slot confirmed", [
                            'faculty'      => $assignment->faculty_name,
                            'faculty_code' => $assignment->faculty_code,
                            'subject'      => $assignment->subject_name,
                            'day'          => $day,
                            'exam_date'    => $specificExamDate,
                            'time'         => "{$slot['start']}–{$slot['end']}",
                            'classroom'    => $classroom->room_name ?? $classroom->name,
                        ]);

                        return [
                            'faculty_id'    => $assignment->faculty_id,
                            'faculty_code'  => $assignment->faculty_code,
                            'subject_id'    => $assignment->subject_id,
                            'classroom_id'  => $classroom->id,
                            'exam_date'     => $specificExamDate,
                            'day'           => $day,
                            'day_name'      => $day,
                            'start_time'    => $slot['start'],
                            'end_time'      => $slot['end'],
                            'exam_type'     => 'Final',
                            'faculty_name'  => $assignment->faculty_name,
                            'course_subject' => $assignment->subject_name,
                            'course_code'   => $assignment->course_code,
                            'units'         => $totalUnits,
                            'year_section'  => $yearSection,
                            'classroom_name' => $classroom->room_name ?? $classroom->name ?? 'Room ' . $classroom->id,
                            'year_level'    => $assignment->year_level,
                            'semester'      => $assignment->semester,
                        ];
                    }
                }
            }
        }

        // No available exam slot found
        $unavailabilityDetails = $this->getFacultyUnavailabilitySummary($assignment->faculty_code);

        Log::warning("❌ [EXAM SCHEDULING FAILED] No available exam slots", [
            'faculty'          => $assignment->faculty_name,
            'faculty_code'     => $assignment->faculty_code,
            'subject'          => $assignment->subject_name,
            'unavailability'   => $unavailabilityDetails,
            'has_restrictions' => !empty($facultyUnavailabilities),
        ]);

        return null;
    }

    private function isExamSlotAvailableForAssignment($exams, $date, $slot, $classroomId, $assignment): bool
    {
        $assignmentSection = $assignment->year_level . '-A';

        foreach ($exams as $exam) {
            if ($exam['exam_date'] !== $date) continue;
            if (!$this->timesOverlap($slot['start'], $slot['end'], $exam['start_time'], $exam['end_time'])) continue;

            if ($exam['classroom_id'] == $classroomId) return false;
            if (($exam['year_section'] ?? null) === $assignmentSection) return false;
        }

        return true;
    }

    // ============================================================================
    // UTILITY / REPORTING
    // ============================================================================

    private function convertDayToNumber($dayName): int
    {
        return $this->dayNameToNumber[$dayName] ?? 1;
    }

    /**
     * Check if two time ranges overlap.
     * Returns true when start1 < end2 AND end1 > start2.
     */
    private function timesOverlap($start1, $end1, $start2, $end2): bool
    {
        return ($start1 < $end2) && ($end1 > $start2);
    }

    private function getNextDateForDay($dayName): string
    {
        return Carbon::parse("next $dayName")->format('Y-m-d');
    }

    private function getDateForDayInFuture($dayName, $weeks): string
    {
        return Carbon::now()->addWeeks($weeks)->next($dayName)->format('Y-m-d');
    }

    /**
     * Get all unavailability slots for a faculty member (formatted for display).
     */
    private function getFacultyUnavailabilitySlots($facultyCode): array
    {
        try {
            $unavailabilities = $this->loadFacultyUnavailability($facultyCode);
            $slots = [];

            foreach ($unavailabilities as $dayNum => $records) {
                $dayName = array_search($dayNum, $this->dayNameToNumber) ?: "Day {$dayNum}";
                foreach ($records as $unavail) {
                    $slots[] = [
                        'day'        => $unavail->day,
                        'day_name'   => $dayName,
                        'start_time' => $unavail->start_time,
                        'end_time'   => $unavail->end_time,
                        'formatted'  => "{$dayName} {$unavail->start_time}–{$unavail->end_time}",
                    ];
                }
            }

            return $slots;
        } catch (Exception $e) {
            Log::warning("Error fetching faculty unavailability slots: " . $e->getMessage());
            return [];
        }
    }

    private function getFacultyUnavailabilitySummary($facultyCode): string
    {
        try {
            $unavailabilities = $this->loadFacultyUnavailability($facultyCode);

            if (empty($unavailabilities)) {
                return 'No unavailability restrictions';
            }

            $summary = [];
            foreach ($unavailabilities as $dayNum => $records) {
                $dayName = array_search($dayNum, $this->dayNameToNumber) ?: "Day {$dayNum}";
                foreach ($records as $unavail) {
                    $summary[] = "{$dayName}: {$unavail->start_time}–{$unavail->end_time}";
                }
            }

            return implode(', ', $summary);
        } catch (Exception $e) {
            return 'Unable to fetch unavailability';
        }
    }

    /**
     * Log a full summary of all faculty who have unavailability restrictions.
     * Called at the start of each generation run.
     */
    private function logUnavailabilitySummary(): void
    {
        $facultiesWithUnavailability = DB::table('faculty_unavailabilities')
            ->select('faculty_code')
            ->distinct()
            ->get();

        if ($facultiesWithUnavailability->isEmpty()) {
            Log::info("No faculty unavailability restrictions found");
            return;
        }

        Log::info("=== FACULTY UNAVAILABILITY SUMMARY ===");
        Log::info("Found {$facultiesWithUnavailability->count()} faculties with unavailability restrictions");

        foreach ($facultiesWithUnavailability as $faculty) {
            $slots = $this->getFacultyUnavailabilitySlots($faculty->faculty_code);
            Log::info("Faculty {$faculty->faculty_code} unavailable slots:", [
                'count' => count($slots),
                'slots' => array_map(fn($s) => $s['formatted'], $slots),
            ]);
        }

        Log::info("=== END UNAVAILABILITY SUMMARY ===");
    }

    // ============================================================================
    // SAVE / PERSIST
    // ============================================================================

    /**
     * Save schedules and/or examinations to database.
     * Schedules → 'schedules' table.  Examinations → 'examinations' table.
     */
    public function saveSchedule($schedules = [], $examinations = [])
    {
        try {
            Log::info('SchedulerService: Starting saveSchedule', [
                'schedule_count' => count($schedules),
                'exam_count'     => count($examinations),
            ]);

            DB::beginTransaction();

            $savedSchedules = 0;
            $savedExams     = 0;
            $errors         = [];

            foreach ($schedules as $index => $schedule) {
                try {
                    if (empty($schedule['faculty_id']))  { $errors[] = "Schedule {$index}: Missing faculty_id";  continue; }
                    if (empty($schedule['subject_id']))  { $errors[] = "Schedule {$index}: Missing subject_id";  continue; }
                    if (empty($schedule['classroom_id'])) { $errors[] = "Schedule {$index}: Missing classroom_id"; continue; }

                    $startTime = $this->ensureTimeFormat($schedule['start_time']);
                    $endTime   = $this->ensureTimeFormat($schedule['end_time']);
                    $dayName   = $schedule['day_name'] ?? $schedule['day'];
                    $dayNumber = $this->convertDayToNumber($dayName);

                    $currentYear = now()->year;
                    $nextYear    = $currentYear + 1;

                    Schedule::create([
                        'faculty_id'    => $schedule['faculty_id'],
                        'faculty_code'  => $schedule['faculty_code'] ?? null,
                        'subject_id'    => $schedule['subject_id'],
                        'classroom_id'  => $schedule['classroom_id'],
                        'day'           => $dayNumber,
                        'start_time'    => $startTime,
                        'end_time'      => $endTime,
                        'class_type'    => $schedule['class_type'] ?? 'Lecture',
                        'year_level'    => $schedule['year_level'] ?? null,
                        'semester'      => $schedule['semester'] ?? null,
                        'schedule_date' => $schedule['schedule_date'] ?? null,
                        'section'       => $schedule['year_section'] ?? null,
                        'is_active'     => true,
                        'academic_year' => "{$currentYear}-{$nextYear}",
                    ]);

                    $savedSchedules++;

                } catch (Exception $e) {
                    $error = "Error saving schedule {$index}: " . $e->getMessage();
                    Log::error($error, ['schedule' => $schedule, 'error' => $e->getTraceAsString()]);
                    $errors[] = $error;
                }
            }

            foreach ($examinations as $index => $exam) {
                try {
                    if (empty($exam['faculty_id']) || empty($exam['subject_id']) || empty($exam['classroom_id'])) {
                        $errors[] = "Exam {$index}: Missing required IDs";
                        continue;
                    }

                    $startTime = $this->ensureTimeFormat($exam['start_time']);
                    $endTime   = $this->ensureTimeFormat($exam['end_time']);
                    $examDate  = Carbon::parse($exam['exam_date']);
                    $dayNumber = $this->convertDayToNumber($examDate->format('l'));

                    Examination::create([
                        'faculty_id'   => $exam['faculty_id'],
                        'faculty_code' => $exam['faculty_code'] ?? null,
                        'subject_id'   => $exam['subject_id'],
                        'classroom_id' => $exam['classroom_id'],
                        'exam_date'    => $exam['exam_date'] ?? null,
                        'day'          => $dayNumber,
                        'start_time'   => $startTime,
                        'end_time'     => $endTime,
                        'exam_type'    => $exam['exam_type'] ?? 'Final',
                        'year_section' => $exam['year_section'] ?? null,
                        'is_active'    => true,
                    ]);

                    $savedExams++;

                } catch (Exception $e) {
                    $error = "Error saving exam {$index}: " . $e->getMessage();
                    Log::error($error, ['exam' => $exam, 'trace' => $e->getTraceAsString()]);
                    $errors[] = $error;
                }
            }

            if ($savedSchedules === 0 && $savedExams === 0 && count($errors) > 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to save schedules. Errors: ' . implode('; ', array_slice($errors, 0, 3)),
                ];
            }

            DB::commit();

            Log::info("Successfully saved {$savedSchedules} schedules and {$savedExams} examinations");

            $message = "Successfully saved {$savedSchedules} schedules and {$savedExams} examinations";
            if (count($errors) > 0) $message .= " (with " . count($errors) . " errors)";

            return [
                'success'         => true,
                'message'         => $message,
                'saved_schedules' => $savedSchedules,
                'saved_exams'     => $savedExams,
                'errors'          => $errors,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SchedulerService: Critical error in saveSchedule', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function saveExaminations($examinations)
    {
        return $this->saveSchedule([], $examinations);
    }

    // ============================================================================
    // DB / MODEL HELPERS
    // ============================================================================

    private function ensureTimeFormat($time): string
    {
        if (empty($time)) throw new Exception('Time value cannot be empty');

        $time = trim($time);
        if (strlen($time) === 5) return $time . ':00';
        if (strlen($time) === 8) return $time;

        throw new Exception("Invalid time format: {$time}");
    }

    public function getPreviousSchedules()
    {
        return Schedule::with(['faculty', 'subject', 'classroom'])
            ->orderBy('schedule_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->groupBy('schedule_date');
    }

    public function clearAllSchedules()
    {
        try {
            DB::beginTransaction();
            $scheduleCount = Schedule::count();
            $examCount     = Examination::count();
            Schedule::truncate();
            Examination::truncate();
            DB::commit();
            Log::info("Cleared {$scheduleCount} schedules and {$examCount} examinations");
            return ['success' => true, 'message' => "Successfully cleared {$scheduleCount} schedules and {$examCount} examinations"];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error clearing schedules: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error clearing schedules: ' . $e->getMessage()];
        }
    }

    public function clearAllExaminations()
    {
        try {
            DB::beginTransaction();
            $examCount = Examination::count();
            Examination::truncate();
            DB::commit();
            Log::info("Cleared {$examCount} examinations");
            return ['success' => true, 'message' => "Successfully cleared {$examCount} examinations"];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error clearing examinations: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error clearing examinations: ' . $e->getMessage()];
        }
    }

    // ============================================================================
    // DIAGNOSTIC / TEST
    // ============================================================================

    /**
     * Test method to verify faculty unavailability is being respected.
     *
     * Usage in a controller:
     *   $result = app(SchedulerService::class)->testFacultyUnavailability('FAC001');
     *   return response()->json($result);
     */
    public function testFacultyUnavailability($facultyCode): array
    {
        $unavailabilities = $this->loadFacultyUnavailability($facultyCode);

        if (empty($unavailabilities)) {
            return [
                'faculty_code'       => $facultyCode,
                'has_unavailability' => false,
                'message'            => 'No unavailability restrictions found',
            ];
        }

        $details = [];
        foreach ($unavailabilities as $dayNum => $records) {
            $dayName = array_search((int) $dayNum, $this->dayNameToNumber) ?: "Day {$dayNum}";
            foreach ($records as $record) {
                $details[] = [
                    'day'        => $dayName,
                    'day_number' => $record->day,
                    'start_time' => $record->start_time,
                    'end_time'   => $record->end_time,
                ];
            }
        }

        $count = array_sum(array_map('count', $unavailabilities));

        return [
            'faculty_code'       => $facultyCode,
            'has_unavailability' => true,
            'count'              => $count,
            'details'            => $details,
            'message'            => "Found {$count} unavailability restrictions for faculty {$facultyCode}",
        ];
    }

    /**
     * Verify that a specific time slot would be blocked for a faculty member.
     * Useful for testing individual rules from a controller or Tinker.
     *
     * Example:
     *   $result = app(SchedulerService::class)
     *       ->verifySlotBlocked('FAC001', 'Monday', '07:00:00', '19:00:00');
     *   // Returns ['blocked' => true, ...]
     */
    public function verifySlotBlocked(string $facultyCode, string $day, string $startTime, string $endTime): array
    {
        $blocked = $this->isFacultyUnavailable($facultyCode, $day, $startTime, $endTime);

        return [
            'faculty_code' => $facultyCode,
            'day'          => $day,
            'start_time'   => $startTime,
            'end_time'     => $endTime,
            'blocked'      => $blocked,
            'message'      => $blocked
                ? "✅ Correctly blocked — faculty is unavailable during {$day} {$startTime}–{$endTime}"
                : "⚠️ NOT blocked — faculty is considered available during {$day} {$startTime}–{$endTime}",
        ];
    }
}
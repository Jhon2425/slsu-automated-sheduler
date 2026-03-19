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
    private $unavailabilityCache = [];

    // ============================================================================
    // CORE: Load and cache unavailability for a faculty member
    // ============================================================================

    private function loadFacultyUnavailability(string $facultyCode): array
    {
        if (array_key_exists($facultyCode, $this->unavailabilityCache)) {
            return $this->unavailabilityCache[$facultyCode];
        }

        try {
            $rows = DB::table('faculty_unavailabilities')
                ->where('faculty_code', $facultyCode)
                ->get();

            $grouped = [];
            foreach ($rows as $record) {
                $raw = (array) $record;

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

                $normalised              = new \stdClass();
                $normalised->day         = $dayInt;
                $normalised->start_time  = $start;
                $normalised->end_time    = $end;
                $normalised->faculty_code = $facultyCode;

                $grouped[$dayInt][] = $normalised;
            }

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
            Log::error("CRITICAL: Failed to load unavailability for faculty {$facultyCode}", [
                'faculty_code' => $facultyCode,
                'error'        => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function isFacultyUnavailable($facultyCode, $day, string $startTime, string $endTime): bool
    {
        $dayNumber = is_numeric($day) ? (int) $day : ($this->dayNameToNumber[$day] ?? null);

        if ($dayNumber === null) {
            Log::warning("isFacultyUnavailable: unknown day value '{$day}' for faculty {$facultyCode} — treating as AVAILABLE");
            return false;
        }

        $unavailabilities = $this->loadFacultyUnavailability($facultyCode);

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
                return true;
            }
        }

        return false;
    }

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

    private function isDayFullyBlocked(string $facultyCode, string $dayName): bool
    {
        return $this->isFacultyUnavailable($facultyCode, $dayName, '07:00:00', '19:00:00');
    }

    // ============================================================================
    // SPLIT-SESSION DISTRIBUTION
    // ============================================================================

    /**
     * Split a total number of hours into smaller chunks for multi-day scheduling.
     *
     * Rules:
     *   - Minimum chunk size: 1 hour
     *   - Maximum chunk size: 3 hours (keeps sessions digestible)
     *   - Prefer splitting into 2-hour + remainder when total > 2
     *   - Randomise split points so every run produces varied schedules
     *
     * Examples:
     *   3 hours → [2, 1]  or  [1, 2]
     *   4 hours → [2, 2]
     *   5 hours → [2, 3]  or  [3, 2]  or  [2, 2, 1] etc.
     *   6 hours → [3, 3]  or  [2, 2, 2]
     *
     * @param  int $totalHours
     * @return int[]  Array of hour chunks that sum to $totalHours
     */
    private function splitHoursAcrossDays(int $totalHours): array
    {
        if ($totalHours <= 2) {
            // 1 or 2 hours: keep as a single session — splitting further gains nothing
            return [$totalHours];
        }

        $chunks  = [];
        $remaining = $totalHours;

        // Allowed chunk sizes (largest first so we fill days efficiently)
        $allowedChunks = [3, 2, 1];

        while ($remaining > 0) {
            // How many chunks do we still want to create?
            $chunksLeft = max(1, intdiv($remaining, 2));

            // Pick a random valid chunk size
            shuffle($allowedChunks);
            $chunk = null;
            foreach ($allowedChunks as $candidate) {
                // Don't leave a 0-hour remainder; also don't exceed remaining
                if ($candidate <= $remaining && ($remaining - $candidate) !== 0 || $candidate === $remaining) {
                    $chunk = $candidate;
                    break;
                }
            }

            // Safety fallback: just take 1 hour at a time
            if ($chunk === null) {
                $chunk = 1;
            }

            $chunks[]  = $chunk;
            $remaining -= $chunk;
        }

        // Shuffle the final order so days are assigned randomly
        shuffle($chunks);

        Log::debug("🔀 [SPLIT] {$totalHours}h split into chunks", ['chunks' => $chunks]);

        return $chunks;
    }

    /**
     * Build the full multi-day session distribution for a faculty-subject assignment.
     *
     * Lecture hours  → split into N chunks, each tagged as 'Lecture'
     * Laboratory hours (already converted to contact hours) → split into M chunks,
     * each tagged as 'Laboratory'
     *
     * The caller receives a flat list like:
     *   [
     *     ['type' => 'Lecture',    'hours' => 2],
     *     ['type' => 'Lecture',    'hours' => 1],
     *     ['type' => 'Laboratory', 'hours' => 3],
     *   ]
     * and each entry is scheduled on a DIFFERENT day.
     *
     * @param  float $lectureUnits
     * @param  float $labUnits      Raw lab units (will be ×3 for contact hours)
     * @return array
     */
    private function getClassDistributionFromFacultySubject($lectureUnits, $labUnits): array
    {
        $distribution = [];

        // ── Lecture ──────────────────────────────────────────────────────────────
        if ($lectureUnits > 0) {
            $lectureHours  = (int) $lectureUnits;               // 1 unit = 1 contact hour
            $lectureChunks = $this->splitHoursAcrossDays($lectureHours);

            foreach ($lectureChunks as $chunkHours) {
                $distribution[] = ['type' => 'Lecture', 'hours' => $chunkHours];
            }

            Log::info("📚 [DISTRIBUTION] Lecture split", [
                'lecture_units'  => $lectureUnits,
                'lecture_hours'  => $lectureHours,
                'chunks'         => $lectureChunks,
            ]);
        }

        // ── Laboratory ───────────────────────────────────────────────────────────
        if ($labUnits > 0) {
            $labContactHours = (int) ($labUnits * 3);           // 1 lab unit = 3 contact hours
            $labChunks       = $this->splitHoursAcrossDays($labContactHours);

            foreach ($labChunks as $chunkHours) {
                $distribution[] = ['type' => 'Laboratory', 'hours' => $chunkHours];
            }

            Log::info("🔬 [DISTRIBUTION] Laboratory split", [
                'lab_units'        => $labUnits,
                'lab_contact_hours' => $labContactHours,
                'chunks'           => $labChunks,
            ]);
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
    // EXAMINATION GENERATION
    // ============================================================================

    public function generateExaminationPreview()
    {
        try {
            Log::info('=== EXAMINATION GENERATION START ===');
            $this->unavailabilityCache = [];

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
                        'assignment_id'       => $assignment->assignment_id,
                        'faculty_code'        => $assignment->faculty_code,
                        'faculty'             => $assignment->faculty_name,
                        'subject'             => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'reason'              => 'Could not find available examination slot' . $unavailabilityDetails,
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
                    'total_exams'     => count($examinations),
                    'total_conflicts' => count($conflicts),
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

    public function generateSchedulePreview()
    {
        try {
            Log::info('=== SCHEDULE GENERATION START ===');
            $this->unavailabilityCache = [];

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

                $this->loadFacultyUnavailability($assignment->faculty_code);

                // getClassDistributionFromFacultySubject now returns MULTIPLE entries
                // per type (one per split chunk), each requiring its own unique day
                $distribution = $this->getClassDistributionFromFacultySubject($lectureUnits, $labUnits);

                Log::info("📋 [SESSION PLAN] Assignment distribution", [
                    'faculty'      => $assignment->faculty_name,
                    'subject'      => $assignment->subject_name,
                    'lecture_units' => $lectureUnits,
                    'lab_units'    => $labUnits,
                    'sessions'     => $distribution,
                ]);

                $scheduled    = false;
                $attemptCount = 0;
                $maxAttempts  = 100;

                while (!$scheduled && $attemptCount < $maxAttempts) {
                    $attemptCount++;

                    // Re-split on each retry so we try different hour combinations
                    if ($attemptCount > 1) {
                        $distribution = $this->getClassDistributionFromFacultySubject($lectureUnits, $labUnits);
                    }

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
                        'assignment_id'       => $assignment->assignment_id,
                        'faculty_code'        => $assignment->faculty_code,
                        'faculty'             => $assignment->faculty_name,
                        'subject'             => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'lecture_units'       => $lectureUnits,
                        'laboratory_units'    => $labUnits,
                        'total_units'         => $totalUnits,
                        'reason'              => 'Could not find available time slots after ' . $maxAttempts . ' attempts.' . $unavailabilityDetails,
                        'unavailability_count' => count($unavailabilitySlots),
                        'unavailable_periods' => $slotDescriptions,
                    ];

                    Log::warning("❌ CONFLICT: Unable to schedule", [
                        'faculty'               => $assignment->faculty_name,
                        'faculty_code'          => $assignment->faculty_code,
                        'subject'               => $assignment->subject_name,
                        'attempts'              => $maxAttempts,
                        'unavailable_slots'     => count($unavailabilitySlots),
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
                'examinations' => [],
                'conflicts'    => $conflicts,
                'message'      => count($schedules) . ' schedule sessions generated successfully',
                'stats'        => [
                    'total_schedules' => count($schedules),
                    'total_exams'     => 0,
                    'total_conflicts' => count($conflicts),
                    'faculty_count'   => count($facultyAssignments),
                ],
            ];

        } catch (Exception $e) {
            Log::error('SchedulerService generateSchedulePreview error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Error generating schedule: ' . $e->getMessage(), 'schedules' => [], 'examinations' => [], 'conflicts' => []];
        }
    }

    // ============================================================================
    // SLOT FINDING — APPLIES TO BOTH LECTURE AND LABORATORY
    // ============================================================================

    /**
     * Schedule every session chunk in the distribution onto a unique day.
     *
     * KEY CHANGE: because getClassDistributionFromFacultySubject() now returns
     * multiple entries per type (e.g. two Lecture entries for a 3-unit subject),
     * each entry MUST land on a different day.  The $usedDays array accumulates
     * days as sessions are placed so the next iteration cannot reuse them.
     */
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
                // Combine already-used days (from previous calls) with days used
                // in the current batch so we never reuse a day within one assignment
                array_merge($usedDays, $existingDays)
            );

            if (!$slot) return false;

            $sessionSchedules[] = $slot;
            $usedDays[]         = $slot['day_name'];
        }

        return $sessionSchedules;
    }

    /**
     * Find an available time slot for a single session chunk.
     * Enforces faculty unavailability as a mandatory hard gate.
     */
    private function findAvailableSlotForAssignment($assignment, $hours, $classType, $classrooms, $existingSchedules, $usedDays = [])
    {
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

        $shuffledDays       = $this->daysOfWeek;
        shuffle($shuffledDays);
        $shuffledClassrooms = $classrooms->shuffle();

        foreach ($shuffledDays as $day) {

            // Skip days already used for this subject (ensures different-day placement)
            if (in_array($day, $usedDays)) continue;

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
                continue;
            }

            shuffle($allowedSlots);

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

        $unavailabilityDetails = $this->getFacultyUnavailabilitySummary($assignment->faculty_code);

        Log::warning("❌ [SCHEDULING FAILED] No available slots found", [
            'faculty'          => $assignment->faculty_name,
            'faculty_code'     => $assignment->faculty_code,
            'subject'          => $assignment->subject_name,
            'class_type'       => $classType,
            'hours_needed'     => $hours,
            'unavailability'   => $unavailabilityDetails,
            'has_restrictions' => !empty($facultyUnavailabilities),
        ]);

        return false;
    }

    /**
     * Returns all possible continuous time windows of a given duration.
     * Each window is expressed as a start/end pair.
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
    // SLOT AVAILABILITY
    // ============================================================================

    private function isSlotAvailableForAssignment($schedules, $day, $startTime, $endTime, $classroomId, $assignment): bool
    {
        $assignmentSection = $assignment->year_level . '-A';

        // Gate 1: Faculty Unavailability (safety-net / second gate)
        if ($this->isFacultyUnavailable($assignment->faculty_code, $day, $startTime, $endTime)) {
            Log::debug("❌ [UNAVAILABILITY BLOCK — SAFETY NET] Faculty blocked at second gate", [
                'faculty_code' => $assignment->faculty_code,
                'faculty_name' => $assignment->faculty_name,
                'day'          => $day,
                'time'         => "{$startTime}–{$endTime}",
            ]);
            return false;
        }

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

    private function generateExaminationForAssignment($assignment, $classrooms, $existingExams, $totalUnits)
    {
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
                            'faculty_id'     => $assignment->faculty_id,
                            'faculty_code'   => $assignment->faculty_code,
                            'subject_id'     => $assignment->subject_id,
                            'classroom_id'   => $classroom->id,
                            'exam_date'      => $specificExamDate,
                            'day'            => $day,
                            'day_name'       => $day,
                            'start_time'     => $slot['start'],
                            'end_time'       => $slot['end'],
                            'exam_type'      => 'Final',
                            'faculty_name'   => $assignment->faculty_name,
                            'course_subject' => $assignment->subject_name,
                            'course_code'    => $assignment->course_code,
                            'units'          => $totalUnits,
                            'year_section'   => $yearSection,
                            'classroom_name' => $classroom->room_name ?? $classroom->name ?? 'Room ' . $classroom->id,
                            'year_level'     => $assignment->year_level,
                            'semester'       => $assignment->semester,
                        ];
                    }
                }
            }
        }

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
                    if (empty($schedule['faculty_id']))   { $errors[] = "Schedule {$index}: Missing faculty_id";  continue; }
                    if (empty($schedule['subject_id']))   { $errors[] = "Schedule {$index}: Missing subject_id";  continue; }
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

    /**
     * Test the hour-splitting logic in isolation.
     * Call from Tinker: app(SchedulerService::class)->testSplitHours(3, 1)
     *
     * @param float $lectureUnits
     * @param float $labUnits
     */
    public function testSplitHours(float $lectureUnits, float $labUnits): array
    {
        $distribution = $this->getClassDistributionFromFacultySubject($lectureUnits, $labUnits);

        $lectureSessions = array_filter($distribution, fn($s) => $s['type'] === 'Lecture');
        $labSessions     = array_filter($distribution, fn($s) => $s['type'] === 'Laboratory');

        return [
            'lecture_units'         => $lectureUnits,
            'lab_units'             => $labUnits,
            'lecture_contact_hours' => (int) $lectureUnits,
            'lab_contact_hours'     => (int) ($labUnits * 3),
            'total_sessions'        => count($distribution),
            'lecture_sessions'      => array_values($lectureSessions),
            'lab_sessions'          => array_values($labSessions),
            'full_distribution'     => $distribution,
            'days_required'         => count($distribution),  // one unique day per session
        ];
    }
}
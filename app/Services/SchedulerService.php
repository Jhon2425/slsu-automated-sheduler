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

    private $dayNameToNumber = [
        'Monday'    => 1,
        'Tuesday'   => 2,
        'Wednesday' => 3,
        'Thursday'  => 4,
        'Friday'    => 5,
        'Saturday'  => 6,
        'Sunday'    => 7
    ];

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

    private $unavailabilityCache = [];

    // ============================================================================
    // OJT HOURS — ROUND FOR TIMETABLE
    // ============================================================================

    /**
     * Round the stored decimal OJT hours to the nearest integer for timetable
     * slot allocation.
     *
     * Rule (standard rounding):
     *   < .5  → round DOWN  (e.g. 4.44 → 4)
     *   >= .5 → round UP    (e.g. 4.50 → 5, 9.72 → 10)
     *
     * Minimum 1 hour so a subject always gets at least one slot.
     */
    private function roundOjtHoursForTimetable(float $decimalHours): int
    {
        return max(1, (int) round($decimalHours));
    }

    // ============================================================================
    // UNAVAILABILITY CACHE
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

                $normalised               = new \stdClass();
                $normalised->day          = $dayInt;
                $normalised->start_time   = $start;
                $normalised->end_time     = $end;
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
                    'faculty_code'    => $facultyCode,
                    'day'             => $day,
                    'requested_time'  => "{$startTime}–{$endTime}",
                    'blocked_by_rule' => "{$record->start_time}–{$record->end_time}",
                ]);
                return true;
            }
        }

        return false;
    }

    private function getAvailableTimeSlots(string $facultyCode, string $dayName, int $hours): array
    {
        $all     = $this->getContinuousTimeSlots($hours);
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

    private function splitHoursAcrossDays(int $totalHours): array
    {
        if ($totalHours <= 2) {
            return [$totalHours];
        }

        $chunks        = [];
        $remaining     = $totalHours;
        $allowedChunks = [3, 2, 1];

        while ($remaining > 0) {
            shuffle($allowedChunks);
            $chunk = null;
            foreach ($allowedChunks as $candidate) {
                if ($candidate <= $remaining && ($remaining - $candidate) !== 0 || $candidate === $remaining) {
                    $chunk = $candidate;
                    break;
                }
            }

            if ($chunk === null) {
                $chunk = 1;
            }

            $chunks[]  = $chunk;
            $remaining -= $chunk;
        }

        shuffle($chunks);

        Log::debug("🔀 [SPLIT] {$totalHours}h split into chunks", ['chunks' => $chunks]);

        return $chunks;
    }

    /**
     * Build the full multi-day session distribution for a faculty-subject assignment.
     *
     * 1. OJT subject  → round decimal, split into OJT-typed chunks, return early
     * 2. Lecture units → 1 unit = 1 contact hour, split into Lecture chunks
     * 3. Lab units     → 1 unit = 3 contact hours, split into Laboratory chunks
     */
    private function getClassDistributionFromFacultySubject(
        float $lectureUnits,
        float $labUnits,
        ?float $ojtHoursDecimal = null
    ): array {
        $distribution = [];

        // ── OJT ──────────────────────────────────────────────────────────────────
        if ($ojtHoursDecimal !== null && $ojtHoursDecimal > 0) {
            $timetableHours = $this->roundOjtHoursForTimetable($ojtHoursDecimal);
            $ojtChunks      = $this->splitHoursAcrossDays($timetableHours);

            foreach ($ojtChunks as $chunkHours) {
                $distribution[] = ['type' => 'OJT', 'hours' => $chunkHours];
            }

            Log::info("🏢 [DISTRIBUTION] OJT split", [
                'ojt_hours_decimal' => $ojtHoursDecimal,
                'timetable_hours'   => $timetableHours,
                'chunks'            => $ojtChunks,
            ]);

            return $distribution; // OJT has no lecture/lab — return early
        }

        // ── Lecture ───────────────────────────────────────────────────────────────
        if ($lectureUnits > 0) {
            $lectureHours  = (int) $lectureUnits;
            $lectureChunks = $this->splitHoursAcrossDays($lectureHours);

            foreach ($lectureChunks as $chunkHours) {
                $distribution[] = ['type' => 'Lecture', 'hours' => $chunkHours];
            }

            Log::info("📚 [DISTRIBUTION] Lecture split", [
                'lecture_units' => $lectureUnits,
                'lecture_hours' => $lectureHours,
                'chunks'        => $lectureChunks,
            ]);
        }

        // ── Laboratory ────────────────────────────────────────────────────────────
        if ($labUnits > 0) {
            $labContactHours = (int) ($labUnits * 3);
            $labChunks       = $this->splitHoursAcrossDays($labContactHours);

            foreach ($labChunks as $chunkHours) {
                $distribution[] = ['type' => 'Laboratory', 'hours' => $chunkHours];
            }

            Log::info("🔬 [DISTRIBUTION] Laboratory split", [
                'lab_units'         => $labUnits,
                'lab_contact_hours' => $labContactHours,
                'chunks'            => $labChunks,
            ]);
        }

        return $distribution;
    }

    /** @deprecated Keep for backward compatibility only */
    private function getClassDistribution($units): array
    {
        $units = (int) $units;
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
                    'faculty_subject.ojt_hours',
                    'faculty_subject.class_size',
                    'users.id as faculty_id',
                    'users.name as faculty_name',
                    'subjects.subject_name',
                    'subjects.course_code',
                    'subjects.year_level',
                    'subjects.semester',
                    DB::raw('(COALESCE(faculty_subject.lecture_units, 0) + COALESCE(faculty_subject.laboratory_units, 0)) as total_units')
                )
                ->get();

            // Include both regular and OJT assignments
            $facultyAssignments = $facultyAssignments->filter(function ($a) {
                $hasRegular = ((float)($a->lecture_units ?? 0) + (float)($a->laboratory_units ?? 0)) > 0;
                $hasOjt     = !is_null($a->ojt_hours) && (float)$a->ojt_hours > 0;
                return $hasRegular || $hasOjt;
            });

            if ($facultyAssignments->isEmpty()) {
                return [
                    'success'      => false,
                    'message'      => 'No faculty-subject assignments found.',
                    'examinations' => [],
                    'conflicts'    => [],
                ];
            }

            $classrooms = Classroom::all();
            if ($classrooms->isEmpty()) {
                return [
                    'success'      => false,
                    'message'      => 'No classrooms found.',
                    'examinations' => [],
                    'conflicts'    => [],
                ];
            }

            $examinations = [];
            $conflicts    = [];

            foreach ($facultyAssignments as $assignment) {
                $ojtDecimal = !is_null($assignment->ojt_hours) && (float)$assignment->ojt_hours > 0
                    ? (float)$assignment->ojt_hours
                    : null;

                // ── OJT subjects have NO examination — skip entirely ──────────
                if ($ojtDecimal !== null) {
                    Log::info('⏭️ [EXAM SKIP] OJT subject — no examination required', [
                        'faculty'   => $assignment->faculty_name,
                        'subject'   => $assignment->subject_name,
                        'ojt_hours' => $ojtDecimal,
                    ]);
                    continue;
                }

                // ── Regular subject ───────────────────────────────────────────
                $totalUnits = (float)($assignment->lecture_units ?? 0)
                            + (float)($assignment->laboratory_units ?? 0);

                if ($totalUnits < 1) continue;

                $exam = $this->generateExaminationForAssignment(
                    $assignment, $classrooms, $examinations, $totalUnits
                );

                if ($exam) {
                    $examinations[] = $exam;
                } else {
                    $unavailabilitySlots   = $this->getFacultyUnavailabilitySlots($assignment->faculty_code);
                    $slotDescriptions      = array_map(fn($slot) => $slot['formatted'], $unavailabilitySlots);
                    $unavailabilityDetails = !empty($slotDescriptions)
                        ? ' ⚠️ Faculty unavailable: ' . implode(', ', $slotDescriptions)
                        : '';

                    $conflicts[] = [
                        'assignment_id'        => $assignment->assignment_id,
                        'faculty_code'         => $assignment->faculty_code,
                        'faculty'              => $assignment->faculty_name,
                        'subject'              => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'reason'               => 'Could not find available examination slot' . $unavailabilityDetails,
                        'unavailability_count' => count($unavailabilitySlots),
                        'unavailable_periods'  => $slotDescriptions,
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
            return [
                'success'      => false,
                'message'      => 'Error generating examinations: ' . $e->getMessage(),
                'examinations' => [],
                'conflicts'    => [],
            ];
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
                    'faculty_subject.ojt_hours',
                    'faculty_subject.class_size',
                    'users.id as faculty_id',
                    'users.name as faculty_name',
                    'subjects.subject_name',
                    'subjects.course_code',
                    'subjects.year_level',
                    'subjects.semester',
                    DB::raw('(COALESCE(faculty_subject.lecture_units, 0) + COALESCE(faculty_subject.laboratory_units, 0)) as total_units')
                )
                ->get();

            // Include both regular and OJT assignments
            $facultyAssignments = $facultyAssignments->filter(function ($a) {
                $hasRegular = ((float)($a->lecture_units ?? 0) + (float)($a->laboratory_units ?? 0)) > 0;
                $hasOjt     = !is_null($a->ojt_hours) && (float)$a->ojt_hours > 0;
                return $hasRegular || $hasOjt;
            });

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
                $ojtDecimal   = !is_null($assignment->ojt_hours) && (float)$assignment->ojt_hours > 0
                    ? (float)$assignment->ojt_hours
                    : null;

                $isOjt = $ojtDecimal !== null;

                if (!$isOjt && ($lectureUnits + $labUnits) < 1) continue;

                $this->loadFacultyUnavailability($assignment->faculty_code);

                $distribution = $this->getClassDistributionFromFacultySubject(
                    $lectureUnits,
                    $labUnits,
                    $ojtDecimal
                );

                if (empty($distribution)) continue;

                Log::info("📋 [SESSION PLAN] Assignment distribution", [
                    'faculty'           => $assignment->faculty_name,
                    'subject'           => $assignment->subject_name,
                    'is_ojt'            => $isOjt,
                    'ojt_decimal'       => $ojtDecimal,
                    'ojt_timetable_hrs' => $isOjt ? $this->roundOjtHoursForTimetable($ojtDecimal) : null,
                    'lecture_units'     => $lectureUnits,
                    'lab_units'         => $labUnits,
                    'sessions'          => $distribution,
                ]);

                $scheduled    = false;
                $attemptCount = 0;
                $maxAttempts  = 100;

                while (!$scheduled && $attemptCount < $maxAttempts) {
                    $attemptCount++;

                    if ($attemptCount > 1) {
                        $distribution = $this->getClassDistributionFromFacultySubject(
                            $lectureUnits,
                            $labUnits,
                            $ojtDecimal
                        );
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
                    $unavailabilitySlots   = $this->getFacultyUnavailabilitySlots($assignment->faculty_code);
                    $slotDescriptions      = array_map(fn($slot) => $slot['formatted'], $unavailabilitySlots);
                    $unavailabilityDetails = !empty($slotDescriptions)
                        ? ' ⚠️ Faculty unavailable: ' . implode(', ', $slotDescriptions)
                        : '';

                    $conflicts[] = [
                        'assignment_id'        => $assignment->assignment_id,
                        'faculty_code'         => $assignment->faculty_code,
                        'faculty'              => $assignment->faculty_name,
                        'subject'              => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'lecture_units'        => $lectureUnits,
                        'laboratory_units'     => $labUnits,
                        'ojt_hours'            => $ojtDecimal,
                        'reason'               => 'Could not find available time slots after ' . $maxAttempts . ' attempts.' . $unavailabilityDetails,
                        'unavailability_count' => count($unavailabilitySlots),
                        'unavailable_periods'  => $slotDescriptions,
                    ];

                    Log::warning("❌ CONFLICT: Unable to schedule", [
                        'faculty'                => $assignment->faculty_name,
                        'faculty_code'           => $assignment->faculty_code,
                        'subject'                => $assignment->subject_name,
                        'is_ojt'                 => $isOjt,
                        'attempts'               => $maxAttempts,
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
                    'faculty_count'   => $facultyAssignments->count(),
                ],
            ];

        } catch (Exception $e) {
            Log::error('SchedulerService generateSchedulePreview error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [
                'success'      => false,
                'message'      => 'Error generating schedule: ' . $e->getMessage(),
                'schedules'    => [],
                'examinations' => [],
                'conflicts'    => [],
            ];
        }
    }

    // ============================================================================
    // SLOT FINDING
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

            // OJT uses lecture rooms (no dedicated lab needed)
            $rooms = $classType === 'Laboratory' ? $labRooms : $lectureRooms;

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

    private function findAvailableSlotForAssignment($assignment, $hours, $classType, $classrooms, $existingSchedules, $usedDays = [])
    {
        $facultyUnavailabilities = $this->loadFacultyUnavailability($assignment->faculty_code);

        if (!empty($facultyUnavailabilities)) {
            $unavailableDays = array_map(function ($dayNum) {
                return array_search((int) $dayNum, $this->dayNameToNumber) ?: "Day {$dayNum}";
            }, array_keys($facultyUnavailabilities));

            $totalRestrictions = array_sum(array_map('count', $facultyUnavailabilities));

            Log::info("🚫 [UNAVAILABILITY ACTIVE] Faculty has restrictions", [
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

            if (in_array($day, $usedDays)) continue;

            $allowedSlots = $this->getAvailableTimeSlots($assignment->faculty_code, $day, $hours);

            if (empty($allowedSlots)) {
                Log::info("⛔ [DAY FULLY BLOCKED] Skipping day", [
                    'faculty_code' => $assignment->faculty_code,
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
                        $ojtDecimal  = !is_null($assignment->ojt_hours ?? null) && (float)($assignment->ojt_hours ?? 0) > 0
                            ? (float)$assignment->ojt_hours
                            : null;
                        $totalUnits  = $ojtDecimal !== null
                            ? $this->roundOjtHoursForTimetable($ojtDecimal)
                            : (float)($assignment->lecture_units ?? 0) + (float)($assignment->laboratory_units ?? 0);

                        Log::info("✅ [SCHEDULED] Slot confirmed", [
                            'faculty'    => $assignment->faculty_name,
                            'subject'    => $assignment->subject_name,
                            'class_type' => $classType,
                            'day'        => $day,
                            'time'       => "{$timeSlot['start']}–{$timeSlot['end']}",
                            'hours'      => $hours,
                        ]);

                        return [
                            'faculty_id'        => $assignment->faculty_id,
                            'faculty_code'      => $assignment->faculty_code,
                            'subject_id'        => $assignment->subject_id,
                            'classroom_id'      => $classroom->id,
                            'day'               => $day,
                            'day_name'          => $day,
                            'start_time'        => $timeSlot['start'],
                            'end_time'          => $timeSlot['end'],
                            'schedule_date'     => $this->getNextDateForDay($day),
                            'class_type'        => $classType,
                            'faculty_name'      => $assignment->faculty_name,
                            'course_subject'    => $assignment->subject_name,
                            'course_code'       => $assignment->course_code,
                            'units'             => $totalUnits,
                            'lecture_units'     => $assignment->lecture_units ?? null,
                            'laboratory_units'  => $assignment->laboratory_units ?? null,
                            'ojt_hours'         => $ojtDecimal,
                            'ojt_hours_rounded' => $ojtDecimal !== null
                                ? $this->roundOjtHoursForTimetable($ojtDecimal)
                                : null,
                            'year_section'      => $yearSection,
                            'classroom_name'    => $classroom->room_name ?? $classroom->name ?? 'Room ' . $classroom->id,
                            'year_level'        => $assignment->year_level,
                            'hours'             => $hours,
                            'semester'          => $assignment->semester,
                        ];
                    }
                }
            }
        }

        $unavailabilityDetails = $this->getFacultyUnavailabilitySummary($assignment->faculty_code);

        Log::warning("❌ [SCHEDULING FAILED] No available slots found", [
            'faculty'        => $assignment->faculty_name,
            'subject'        => $assignment->subject_name,
            'class_type'     => $classType,
            'hours_needed'   => $hours,
            'unavailability' => $unavailabilityDetails,
        ]);

        return false;
    }

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

        if ($this->isFacultyUnavailable($assignment->faculty_code, $day, $startTime, $endTime)) {
            Log::debug("❌ [UNAVAILABILITY BLOCK — SAFETY NET]", [
                'faculty_code' => $assignment->faculty_code,
                'day'          => $day,
                'time'         => "{$startTime}–{$endTime}",
            ]);
            return false;
        }

        foreach ($schedules as $schedule) {
            $scheduleDay = $schedule['day_name'] ?? $schedule['day'];

            if ($scheduleDay !== $day) continue;
            if (!$this->timesOverlap($startTime, $endTime, $schedule['start_time'], $schedule['end_time'])) continue;

            if ($schedule['classroom_id'] == $classroomId) {
                Log::warning("❌ [CLASSROOM CONFLICT]", [
                    'classroom_id'   => $classroomId,
                    'day'            => $day,
                    'requested_time' => "{$startTime}–{$endTime}",
                ]);
                return false;
            }

            if (isset($schedule['faculty_code']) && $schedule['faculty_code'] == $assignment->faculty_code) {
                Log::debug("❌ [FACULTY DOUBLE-BOOKING]", [
                    'faculty_code'   => $assignment->faculty_code,
                    'day'            => $day,
                    'requested_time' => "{$startTime}–{$endTime}",
                ]);
                return false;
            }

            if (($schedule['year_section'] ?? null) === $assignmentSection) {
                Log::debug("❌ [SECTION CONFLICT]", [
                    'section'        => $assignmentSection,
                    'day'            => $day,
                    'requested_time' => "{$startTime}–{$endTime}",
                ]);
                return false;
            }
        }

        return true;
    }

    // ============================================================================
    // EXAMINATION SLOT GENERATION
    // ============================================================================

    private function generateExaminationForAssignment($assignment, $classrooms, $existingExams, $totalUnits)
    {
        $this->loadFacultyUnavailability($assignment->faculty_code);

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

            if (empty($allowedSlots)) continue;

            shuffle($allowedSlots);

            foreach ($allowedSlots as $slot) {
                foreach ($classrooms->shuffle() as $classroom) {
                    $specificExamDate = $this->getDateForDayInFuture($day, $weeksAhead);

                    if ($this->isExamSlotAvailableForAssignment(
                        $existingExams, $specificExamDate, $slot, $classroom->id, $assignment
                    )) {
                        $yearSection = $assignment->year_level . '-A';

                        Log::info("✅ [EXAM SCHEDULED]", [
                            'faculty'   => $assignment->faculty_name,
                            'subject'   => $assignment->subject_name,
                            'day'       => $day,
                            'exam_date' => $specificExamDate,
                            'time'      => "{$slot['start']}–{$slot['end']}",
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

        Log::warning("❌ [EXAM SCHEDULING FAILED]", [
            'faculty' => $assignment->faculty_name,
            'subject' => $assignment->subject_name,
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
                    if (empty($schedule['faculty_id']))   { $errors[] = "Schedule {$index}: Missing faculty_id";   continue; }
                    if (empty($schedule['subject_id']))   { $errors[] = "Schedule {$index}: Missing subject_id";   continue; }
                    if (empty($schedule['classroom_id'])) { $errors[] = "Schedule {$index}: Missing classroom_id"; continue; }

                    $startTime = $this->ensureTimeFormat($schedule['start_time']);
                    $endTime   = $this->ensureTimeFormat($schedule['end_time']);
                    $dayName   = $schedule['day_name'] ?? $schedule['day'];
                    $dayNumber = $this->convertDayToNumber($dayName);

                    $currentYear = now()->year;
                    $nextYear    = $currentYear + 1;

                    Schedule::create([
                        'faculty_id'        => $schedule['faculty_id'],
                        'faculty_code'      => $schedule['faculty_code'] ?? null,
                        'subject_id'        => $schedule['subject_id'],
                        'classroom_id'      => $schedule['classroom_id'],
                        'day'               => $dayNumber,
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'class_type'        => $schedule['class_type'] ?? 'Lecture',
                        'year_level'        => $schedule['year_level'] ?? null,
                        'semester'          => $schedule['semester'] ?? null,
                        'schedule_date'     => $schedule['schedule_date'] ?? null,
                        'section'           => $schedule['year_section'] ?? null,
                        'hours'             => $schedule['hours'] ?? null,
                        'ojt_hours'         => $schedule['ojt_hours'] ?? null,
                        'ojt_hours_rounded' => $schedule['ojt_hours_rounded'] ?? null,
                        'is_active'         => true,
                        'academic_year'     => "{$currentYear}-{$nextYear}",
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

    public function testSplitHours(float $lectureUnits, float $labUnits, ?float $ojtDecimal = null): array
    {
        $distribution = $this->getClassDistributionFromFacultySubject($lectureUnits, $labUnits, $ojtDecimal);

        $lectureSessions = array_filter($distribution, fn($s) => $s['type'] === 'Lecture');
        $labSessions     = array_filter($distribution, fn($s) => $s['type'] === 'Laboratory');
        $ojtSessions     = array_filter($distribution, fn($s) => $s['type'] === 'OJT');

        return [
            'lecture_units'         => $lectureUnits,
            'lab_units'             => $labUnits,
            'ojt_decimal'           => $ojtDecimal,
            'ojt_timetable_hours'   => $ojtDecimal !== null ? $this->roundOjtHoursForTimetable($ojtDecimal) : null,
            'lecture_contact_hours' => (int) $lectureUnits,
            'lab_contact_hours'     => (int) ($labUnits * 3),
            'total_sessions'        => count($distribution),
            'lecture_sessions'      => array_values($lectureSessions),
            'lab_sessions'          => array_values($labSessions),
            'ojt_sessions'          => array_values($ojtSessions),
            'full_distribution'     => $distribution,
            'days_required'         => count($distribution),
        ];
    }
}
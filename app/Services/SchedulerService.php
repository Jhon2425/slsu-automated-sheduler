<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Examination;
use App\Models\Classroom;
use App\Models\Faculty;
use App\Models\FacultySubject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SchedulerService
{
    private array $daysOfWeek = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
    ];

    private array $dayNameToNumber = [
        'Monday' => 1,
        'Tuesday' => 2,
        'Wednesday' => 3,
        'Thursday' => 4,
        'Friday' => 5,
        'Saturday' => 6,
        'Sunday' => 7,
    ];

    private array $timeSlots = [
        ['start' => '07:00:00', 'end' => '08:00:00'],
        ['start' => '08:00:00', 'end' => '09:00:00'],
        ['start' => '09:00:00', 'end' => '10:00:00'],
        ['start' => '10:00:00', 'end' => '11:00:00'],
        ['start' => '11:00:00', 'end' => '12:00:00'],
        ['start' => '12:00:00', 'end' => '13:00:00'],
        ['start' => '13:00:00', 'end' => '14:00:00'],
        ['start' => '14:00:00', 'end' => '15:00:00'],
        ['start' => '15:00:00', 'end' => '16:00:00'],
        ['start' => '16:00:00', 'end' => '17:00:00'],
        ['start' => '17:00:00', 'end' => '18:00:00'],
        ['start' => '18:00:00', 'end' => '19:00:00'],
    ];

    private function convertDayToNumber(string $day): int
    {
        return $this->dayNameToNumber[$day] ?? 1;
    }

    /**
     * ============================
     * GENERATE SCHEDULE PREVIEW
     * ============================
     */
    public function generateSchedulePreview(): array
    {
        try {
            Log::info('=== SCHEDULE GENERATION START ===');

            // First, check if tables exist and have data
            $facultyCount = DB::table('faculty')->count();
            $subjectCount = DB::table('subjects')->count();
            $assignmentCount = DB::table('faculty_subject')->count();
            
            Log::info("Database check - Faculty: {$facultyCount}, Subjects: {$subjectCount}, Assignments: {$assignmentCount}");

            if ($assignmentCount === 0) {
                return [
                    'success' => false,
                    'message' => 'No faculty-subject assignments found in faculty_subject table. Please assign subjects to faculty first.',
                    'schedules' => [],
                    'examinations' => [],
                    'conflicts' => [],
                ];
            }

            /**
             * ✅ FETCH USING PROPER FACULTY_SUBJECT TABLE
             * Join with faculty table (not users) to get faculty name
             */
            $facultyAssignments = DB::table('faculty_subject')
                ->join('faculty', 'faculty_subject.faculty_id', '=', 'faculty.id')
                ->join('subjects', 'faculty_subject.subject_id', '=', 'subjects.id')
                ->select(
                    'faculty_subject.id as assignment_id',
                    'faculty_subject.faculty_id',
                    'faculty_subject.subject_id',
                    'faculty_subject.lecture_units',
                    'faculty_subject.laboratory_units',
                    'faculty_subject.year_level as assignment_year_level',
                    'faculty_subject.semester as assignment_semester',
                    'faculty.name as faculty_name',
                    'faculty.user_id',
                    'subjects.subject_name',
                    'subjects.course_code',
                    'subjects.year_level as subject_year_level',
                    'subjects.semester as subject_semester',
                    DB::raw('
                        COALESCE(faculty_subject.lecture_units,0) +
                        COALESCE(faculty_subject.laboratory_units,0)
                        as total_units
                    ')
                )
                ->havingRaw('total_units > 0')
                ->get();

            Log::info('Faculty assignments with units > 0: ' . $facultyAssignments->count());
            
            if ($facultyAssignments->count() > 0) {
                Log::info('Sample assignment: ' . json_encode($facultyAssignments->first()));
            }

            if ($facultyAssignments->isEmpty()) {
                Log::warning('No faculty assignments with units > 0 found');
                
                // Check if there are assignments without units
                $assignmentsWithoutUnits = DB::table('faculty_subject')->count();
                
                return [
                    'success' => false,
                    'message' => "Found {$assignmentsWithoutUnits} faculty-subject assignments, but none have lecture_units or laboratory_units set. Please set units for faculty assignments.",
                    'schedules' => [],
                    'examinations' => [],
                    'conflicts' => [],
                ];
            }

            $classrooms = Classroom::all();
            if ($classrooms->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No classrooms available.',
                    'schedules' => [],
                    'examinations' => [],
                    'conflicts' => [],
                ];
            }

            $lectureRooms = $classrooms;
            $labRooms = $classrooms;

            $schedules = [];
            $exams = [];
            $conflicts = [];
            $subjectDayUsage = [];

            foreach ($facultyAssignments as $assignment) {
                // Use assignment year_level and semester if available, otherwise fall back to subject
                $yearLevel = $assignment->assignment_year_level ?? $assignment->subject_year_level;
                $semester = $assignment->assignment_semester ?? $assignment->subject_semester;

                Log::info("Processing assignment for {$assignment->faculty_name} - {$assignment->subject_name}");

                $distribution = $this->buildDistribution(
                    (float)$assignment->lecture_units,
                    (float)$assignment->laboratory_units
                );

                if (empty($distribution)) {
                    Log::warning("No distribution for assignment {$assignment->assignment_id}");
                    continue;
                }

                $sessionSet = $this->scheduleSessions(
                    $assignment,
                    $distribution,
                    $lectureRooms,
                    $labRooms,
                    $schedules,
                    $subjectDayUsage,
                    $yearLevel,
                    $semester
                );

                if ($sessionSet === false) {
                    $conflicts[] = [
                        'faculty' => $assignment->faculty_name,
                        'subject' => $assignment->subject_name,
                        'reason' => 'No available slot',
                    ];
                    Log::warning("No slot found for {$assignment->faculty_name} - {$assignment->subject_name}");
                    continue;
                }

                $schedules = array_merge($schedules, $sessionSet);

                // Generate examination
                $exam = $this->generateExam($assignment, $classrooms, $yearLevel, $semester);
                if ($exam) {
                    $exams[] = $exam;
                }
            }

            Log::info('Schedules generated: ' . count($schedules));
            Log::info('Examinations generated: ' . count($exams));
            Log::info('Conflicts: ' . count($conflicts));

            return [
                'success' => true,
                'schedules' => $schedules,
                'examinations' => $exams,
                'conflicts' => $conflicts,
                'message' => count($schedules) . ' schedules and ' . count($exams) . ' examinations generated',
            ];

        } catch (Exception $e) {
            Log::error('Schedule generation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'schedules' => [],
                'examinations' => [],
                'conflicts' => [],
            ];
        }
    }

    /**
     * ============================
     * DISTRIBUTION
     * ============================
     */
    private function buildDistribution(float $lecture, float $lab): array
    {
        $out = [];

        if ($lecture > 0) {
            $out[] = ['type' => 'Lecture', 'hours' => (int)$lecture];
        }

        if ($lab > 0) {
            // Lab units are typically 3 hours per unit
            $out[] = ['type' => 'Laboratory', 'hours' => (int)($lab * 3)];
        }

        return $out;
    }

    /**
     * ============================
     * SESSION SCHEDULER
     * ============================
     */
    private function scheduleSessions(
        $assignment,
        array $distribution,
        $lectureRooms,
        $labRooms,
        array $existing,
        array &$subjectDayUsage,
        $yearLevel,
        $semester
    ) {
        $sessions = [];
        $usedDays = [];

        foreach ($distribution as $block) {
            $slot = $this->findSlot(
                $assignment,
                $block['hours'],
                $block['type'],
                $block['type'] === 'Laboratory' ? $labRooms : $lectureRooms,
                array_merge($existing, $sessions),
                $usedDays,
                $yearLevel,
                $semester
            );

            if (!$slot) {
                return false;
            }

            $sessions[] = $slot;
            $usedDays[] = $slot['day_name'];
        }

        return $sessions;
    }

    private function findSlot($assignment, int $hours, string $type, $rooms, array $existing, array $usedDays, $yearLevel, $semester)
    {
        $days = $this->daysOfWeek;
        shuffle($days);

        foreach ($days as $day) {
            if (in_array($day, $usedDays)) continue;

            foreach ($this->getContinuousSlots($hours) as $slot) {
                foreach ($rooms as $room) {
                    if ($this->slotFree($existing, $day, $slot, $room->id, $assignment, $yearLevel)) {
                        return [
                            'faculty_id' => $assignment->faculty_id,
                            'subject_id' => $assignment->subject_id,
                            'classroom_id' => $room->id,
                            'day_name' => $day,
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                            'class_type' => $type,
                            'faculty_name' => $assignment->faculty_name,
                            'course_code' => $assignment->course_code,
                            'course_subject' => $assignment->subject_name,
                            'classroom_name' => $room->room_name ?? 'N/A',
                            'year_level' => $yearLevel,
                            'semester' => $semester,
                            'year_section' => $yearLevel . '-A',
                            'schedule_date' => Carbon::parse("next $day")->format('Y-m-d'),
                        ];
                    }
                }
            }
        }

        return false;
    }

    private function getContinuousSlots(int $hours): array
    {
        $slots = [];
        for ($i = 0; $i <= count($this->timeSlots) - $hours; $i++) {
            $slots[] = [
                'start' => $this->timeSlots[$i]['start'],
                'end' => $this->timeSlots[$i + $hours - 1]['end'],
            ];
        }
        return $slots;
    }

    private function slotFree(array $existing, string $day, array $slot, int $roomId, $assignment, $yearLevel): bool
    {
        $yearSection = $yearLevel . '-A';

        foreach ($existing as $e) {
            if ($e['day_name'] !== $day) continue;

            // Check for time overlap
            if (
                $slot['start'] < $e['end_time'] &&
                $slot['end'] > $e['start_time']
            ) {
                // Conflict if same room, same faculty, or same year section
                if (
                    $e['classroom_id'] == $roomId ||
                    $e['faculty_id'] == $assignment->faculty_id ||
                    ($e['year_section'] ?? '') === $yearSection
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * ============================
     * EXAM GENERATION
     * ============================
     */
    private function generateExam($assignment, $classrooms, $yearLevel, $semester)
    {
        $day = $this->daysOfWeek[array_rand($this->daysOfWeek)];
        $room = $classrooms->random();

        return [
            'faculty_id' => $assignment->faculty_id,
            'subject_id' => $assignment->subject_id,
            'classroom_id' => $room->id,
            'exam_date' => Carbon::now()->addWeeks(8)->next($day)->format('Y-m-d'),
            'day_name' => $day,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'exam_type' => 'Final',
            'year_section' => $yearLevel . '-A',
            'faculty_name' => $assignment->faculty_name,
            'course_subject' => $assignment->subject_name,
            'course_code' => $assignment->course_code,
            'classroom_name' => $room->room_name ?? 'N/A',
            'year_level' => $yearLevel,
            'semester' => $semester,
        ];
    }
}
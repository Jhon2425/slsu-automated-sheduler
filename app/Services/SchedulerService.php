<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Examination;
use App\Models\Classroom;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SchedulerService
{
    private array $daysOfWeek = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
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

    /**
     * ============================
     * GENERATE SCHEDULE PREVIEW
     * ============================
     */
    public function generateSchedulePreview(): array
    {
        try {
            Log::info('=== SCHEDULE GENERATION START ===');

            $assignmentCount = DB::table('faculty_subject')->count();
            if ($assignmentCount === 0) {
                return $this->fail('No faculty-subject assignments found.');
            }

            // Fetch all assignments (including 0-unit)
            $facultyAssignments = DB::table('faculty_subject')
                ->join('faculty', 'faculty_subject.faculty_id', '=', 'faculty.id')
                ->join('subjects', 'faculty_subject.subject_id', '=', 'subjects.id')
                ->select(
                    'faculty_subject.id',
                    'faculty_subject.faculty_id',
                    'faculty_subject.subject_id',
                    'faculty_subject.lecture_units',
                    'faculty_subject.laboratory_units',
                    'faculty_subject.year_level',
                    'faculty_subject.semester',
                    'faculty.name as faculty_name',
                    'subjects.subject_name',
                    'subjects.course_code'
                )
                ->get();

            if ($facultyAssignments->isEmpty()) {
                return $this->fail('No faculty assignments found.');
            }

            $classrooms = Classroom::all();
            if ($classrooms->isEmpty()) {
                return $this->fail('No classrooms available.');
            }

            $schedules = [];
            $exams = [];
            $conflicts = [];

            foreach ($facultyAssignments as $assignment) {

                $distribution = $this->buildDistribution(
                    (float) $assignment->lecture_units,
                    (float) $assignment->laboratory_units
                );

                $sessionSet = $this->scheduleSessions(
                    $assignment,
                    $distribution,
                    $classrooms,
                    $schedules
                );

                if ($sessionSet === false) {
                    $conflicts[] = [
                        'faculty' => $assignment->faculty_name,
                        'subject' => $assignment->subject_name,
                        'reason'  => 'No available slot'
                    ];
                    continue;
                }

                $schedules = array_merge($schedules, $sessionSet);
                $exams[] = $this->generateExam($assignment, $classrooms);
            }

            return [
                'success' => true,
                'message' => count($schedules) . ' schedules generated',
                'schedules' => $schedules,
                'examinations' => $exams,
                'conflicts' => $conflicts,
            ];

        } catch (Exception $e) {
            Log::error('Schedule generation error', [
                'message' => $e->getMessage()
            ]);

            return $this->fail($e->getMessage());
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
            $out[] = [
                'type' => 'Lecture',
                'hours' => (int) ceil($lecture)
            ];
        }

        if ($lab > 0) {
            $out[] = [
                'type' => 'Laboratory',
                'hours' => max(1, (int) round($lab * 3))
            ];
        }

        // Accept 0-unit: create a placeholder schedule
        if (empty($out)) {
            $out[] = ['type' => 'Lecture', 'hours' => 1];
        }

        return $out;
    }

    /**
     * ============================
     * SESSION SCHEDULER
     * ============================
     */
    private function scheduleSessions($assignment, array $distribution, $rooms, array $existing)
    {
        $sessions = [];
        $usedDays = [];

        foreach ($distribution as $block) {

            $slot = $this->findSlot(
                $assignment,
                $block['hours'],
                $block['type'],
                $rooms,
                array_merge($existing, $sessions),
                $usedDays
            );

            if (!$slot) {
                return false;
            }

            $sessions[] = $slot;
            $usedDays[] = $slot['day_name'];
        }

        return $sessions;
    }

    private function findSlot($assignment, int $hours, string $type, $rooms, array $existing, array $usedDays)
    {
        $days = $this->daysOfWeek;
        shuffle($days);

        foreach ($days as $day) {
            if (in_array($day, $usedDays)) continue;

            foreach ($this->getContinuousSlots($hours) as $slot) {
                foreach ($rooms as $room) {
                    if ($this->slotFree($existing, $day, $slot, $room->id, $assignment)) {
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
                            'year_section' => ($assignment->year_level ?? 1) . '-A',
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
                'end'   => $this->timeSlots[$i + $hours - 1]['end'],
            ];
        }
        return $slots;
    }

    private function slotFree(array $existing, string $day, array $slot, int $roomId, $assignment): bool
    {
        foreach ($existing as $e) {
            if ($e['day_name'] !== $day) continue;

            if (
                $slot['start'] < $e['end_time'] &&
                $slot['end'] > $e['start_time']
            ) {
                if (
                    $e['classroom_id'] == $roomId ||
                    $e['faculty_id'] == $assignment->faculty_id ||
                    ($e['year_section'] ?? '') === (($assignment->year_level ?? 1) . '-A')
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
    private function generateExam($assignment, $classrooms): array
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
            'year_section' => ($assignment->year_level ?? 1) . '-A',
        ];
    }

    /**
     * ============================
     * HELPERS
     * ============================
     */
    private function fail(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'schedules' => [],
            'examinations' => [],
            'conflicts' => [],
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Examination;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SchedulerService
{
    private $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    // Time slots from 7 AM to 7 PM
    private $timeSlots = [
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
     * Generate schedule preview with conflict prevention
     */
    public function generateSchedulePreview()
    {
        try {
            $facultyAssignments = DB::table('faculty_subjects')
                ->join('users', 'faculty_subjects.faculty_id', '=', 'users.id')
                ->join('subjects', 'faculty_subjects.subject_id', '=', 'subjects.id')
                ->select(
                    'faculty_subjects.id as assignment_id',
                    'faculty_subjects.faculty_id',
                    'faculty_subjects.subject_id',
                    'users.name as faculty_name',
                    'subjects.subject_name',
                    'subjects.course_code',
                    'subjects.units',
                    'subjects.year_level',
                    'subjects.semester',
                    'subjects.enrolled_student',
                    'subjects.program_id'
                )
                ->whereNotNull('subjects.units')
                ->where('subjects.units', '>', 0)
                ->get();

            if ($facultyAssignments->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No faculty-subject assignments found. Assign subjects first.',
                    'schedules' => [],
                    'examinations' => [],
                    'conflicts' => []
                ];
            }

            $classrooms = Classroom::all();
            if ($classrooms->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No classrooms found. Add classrooms first.',
                    'schedules' => [],
                    'examinations' => [],
                    'conflicts' => []
                ];
            }

            $lectureRooms = $classrooms->filter(fn($room) => 
                in_array(strtolower($room->room_type ?? ''), ['classroom','lecture','lecture room']) ||
                stripos($room->room_name ?? '', 'lab') === false
            );

            $labRooms = $classrooms->filter(fn($room) => 
                in_array(strtolower($room->room_type ?? ''), ['laboratory','lab','computer lab']) ||
                stripos($room->room_name ?? '', 'lab') !== false
            );

            if ($lectureRooms->isEmpty()) $lectureRooms = $classrooms;
            if ($labRooms->isEmpty()) $labRooms = $classrooms;

            $schedules = [];
            $examinations = [];
            $conflicts = [];
            
            // Track which days each subject has been scheduled
            $subjectDayUsage = [];

            foreach ($facultyAssignments as $assignment) {
                $units = (float) $assignment->units;
                if ($units < 1) continue;

                // Get proper distribution based on units
                $distribution = $this->getClassDistribution($units);

                $scheduled = false;
                $attemptCount = 0;
                $maxAttempts = 100;

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

                        // Track days used by this subject
                        $subjectKey = $assignment->year_level . '_' . $assignment->subject_id;
                        foreach ($sessionSchedules as $session) {
                            if (!isset($subjectDayUsage[$subjectKey])) {
                                $subjectDayUsage[$subjectKey] = [];
                            }
                            $subjectDayUsage[$subjectKey][] = $session['day_name'];
                        }

                        // Generate examination
                        $exam = $this->generateExaminationForAssignment($assignment, $classrooms, $examinations);
                        if ($exam) $examinations[] = $exam;
                    }
                }

                if (!$scheduled) {
                    $conflicts[] = [
                        'assignment_id' => $assignment->assignment_id,
                        'faculty' => $assignment->faculty_name,
                        'subject' => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'units' => $units,
                        'reason' => 'Could not find available time slots after ' . $maxAttempts . ' attempts.'
                    ];
                }
            }

            // Format times for frontend display (remove seconds)
            $schedules = array_map(function($schedule) {
                $schedule['start_time'] = substr($schedule['start_time'], 0, 5);
                $schedule['end_time'] = substr($schedule['end_time'], 0, 5);
                return $schedule;
            }, $schedules);

            $examinations = array_map(function($exam) {
                $exam['start_time'] = substr($exam['start_time'], 0, 5);
                $exam['end_time'] = substr($exam['end_time'], 0, 5);
                return $exam;
            }, $examinations);

            return [
                'success' => true,
                'schedules' => $schedules,
                'examinations' => $examinations,
                'conflicts' => $conflicts,
                'message' => count($schedules) . ' schedule sessions generated successfully',
                'stats' => [
                    'total_schedules' => count($schedules),
                    'total_exams' => count($examinations),
                    'total_conflicts' => count($conflicts),
                    'faculty_count' => count($facultyAssignments)
                ]
            ];

        } catch (Exception $e) {
            Log::error('SchedulerService generateSchedulePreview error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Error generating schedule: ' . $e->getMessage(),
                'schedules' => [],
                'examinations' => [],
                'conflicts' => []
            ];
        }
    }

    /**
     * Fixed class distribution - returns CONTINUOUS time blocks
     * 2 units = 2 continuous hours Lecture
     * 3 units = 2 continuous hours Lecture + 1 hour Lab
     * 4 units = 2 continuous hours Lecture + 2 continuous hours Lab
     * 5 units = 3 continuous hours Lecture + 2 continuous hours Lab
     */
    private function getClassDistribution($units)
    {
        $units = (int)$units;
        switch ($units) {
            case 2: 
                return [['type' => 'Lecture', 'hours' => 2]];
                
            case 3: 
                return [
                    ['type' => 'Lecture', 'hours' => 2],
                    ['type' => 'Laboratory', 'hours' => 1]
                ];
                
            case 4: 
                return [
                    ['type' => 'Lecture', 'hours' => 2],
                    ['type' => 'Laboratory', 'hours' => 2]
                ];
                
            case 5: 
                return [
                    ['type' => 'Lecture', 'hours' => 3],
                    ['type' => 'Laboratory', 'hours' => 2]
                ];
                
            default: 
                return [['type' => 'Lecture', 'hours' => $units]];
        }
    }

    private function scheduleAssignmentSessions($assignment, $distribution, $lectureRooms, $labRooms, $existingSchedules, $subjectDayUsage)
    {
        $sessionSchedules = [];
        $usedDays = [];
        
        // Get days already used by this subject
        $subjectKey = $assignment->year_level . '_' . $assignment->subject_id;
        $existingDays = $subjectDayUsage[$subjectKey] ?? [];

        foreach ($distribution as $session) {
            $hours = $session['hours'];
            $classType = $session['type'];
            $rooms = $classType === 'Laboratory' ? $labRooms : $lectureRooms;

            if ($rooms->isEmpty()) return false;

            // Find CONTINUOUS time slot
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
            $usedDays[] = $slot['day_name'];
        }

        return $sessionSchedules;
    }

    /**
     * Find CONTINUOUS time slots (e.g., 2 hours = 8:00-10:00, not separate 8:00-9:00 and 9:00-10:00)
     */
    private function findAvailableSlotForAssignment($assignment, $hours, $classType, $classrooms, $existingSchedules, $usedDays = [])
    {
        $shuffledDays = $this->daysOfWeek; 
        shuffle($shuffledDays);
        $shuffledClassrooms = $classrooms->shuffle();

        foreach ($shuffledDays as $day) {
            // CRITICAL: Skip if subject already scheduled on this day
            if (in_array($day, $usedDays)) {
                continue;
            }
            
            // Get CONTINUOUS time slots (e.g., 2 hours = one block from 8-10, not two separate 8-9 and 9-10)
            $availableSlots = $this->getContinuousTimeSlots($hours); 
            shuffle($availableSlots);

            foreach ($availableSlots as $timeSlot) {
                foreach ($shuffledClassrooms as $classroom) {
                    if ($this->isSlotAvailableForAssignment($existingSchedules, $day, $timeSlot['start'], $timeSlot['end'], $classroom->id, $assignment)) {
                        $yearSection = $assignment->year_level . '-' . ($assignment->enrolled_student ?? 'A');

                        return [
                            'faculty_id'      => $assignment->faculty_id,
                            'subject_id'      => $assignment->subject_id,
                            'classroom_id'    => $classroom->id,
                            'program_id'      => $assignment->program_id ?? null,
                            'day'             => $day,
                            'day_name'        => $day,
                            'start_time'      => $timeSlot['start'],
                            'end_time'        => $timeSlot['end'],
                            'schedule_date'   => $this->getNextDateForDay($day),
                            'class_type'      => $classType,
                            'faculty_name'    => $assignment->faculty_name,
                            'course_subject'  => $assignment->subject_name,
                            'course_code'     => $assignment->course_code,
                            'units'           => $assignment->units,
                            'year_section'    => $yearSection,
                            'classroom_name'  => $classroom->room_name ?? $classroom->name ?? 'Room ' . $classroom->id,
                            'year_level'      => $assignment->year_level,
                            'hours'           => $hours,
                            'semester'        => $assignment->semester
                        ];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get CONTINUOUS time slots
     * Example: For 2 hours, returns [07:00-09:00, 08:00-10:00, 09:00-11:00, etc.]
     * This ensures 2-hour lectures occupy a continuous 2-hour block, not separate hours
     */
    private function getContinuousTimeSlots($hours)
    {
        $continuousSlots = [];
        for ($i = 0; $i <= count($this->timeSlots) - $hours; $i++) {
            $continuousSlots[] = [
                'start' => $this->timeSlots[$i]['start'],
                'end' => $this->timeSlots[$i + $hours - 1]['end']
            ];
        }
        return $continuousSlots;
    }

    private function isSlotAvailableForAssignment($schedules, $day, $startTime, $endTime, $classroomId, $assignment)
    {
        $assignmentSection = $assignment->year_level . '-' . ($assignment->enrolled_student ?? 'A');

        foreach ($schedules as $schedule) {
            $scheduleDay = $schedule['day_name'] ?? $schedule['day'];
            if ($scheduleDay !== $day) continue;
            
            if (!$this->timesOverlap($startTime, $endTime, $schedule['start_time'], $schedule['end_time'])) continue;

            // Classroom conflict
            if ($schedule['classroom_id'] == $classroomId) return false;
            
            // Faculty conflict
            if (isset($schedule['faculty_id']) && $schedule['faculty_id'] == $assignment->faculty_id) return false;
            
            // Student group conflict
            if (($schedule['year_section'] ?? null) === $assignmentSection) return false;
        }

        return true;
    }

    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        return ($start1 < $end2) && ($end1 > $start2);
    }

    private function generateExaminationForAssignment($assignment, $classrooms, $existingExams)
    {
        $weeksAhead = rand(8, 10);
        $shuffledDays = $this->daysOfWeek; 
        shuffle($shuffledDays);

        $examTimeSlots = [
            ['start' => '08:00:00', 'end' => '10:00:00'],
            ['start' => '10:00:00', 'end' => '12:00:00'],
            ['start' => '13:00:00', 'end' => '15:00:00'],
            ['start' => '15:00:00', 'end' => '17:00:00']
        ]; 
        shuffle($examTimeSlots);

        foreach ($shuffledDays as $day) {
            foreach ($examTimeSlots as $slot) {
                foreach ($classrooms->shuffle() as $classroom) {
                    $specificExamDate = $this->getDateForDayInFuture($day, $weeksAhead);

                    if ($this->isExamSlotAvailableForAssignment($existingExams, $specificExamDate, $slot, $classroom->id, $assignment)) {
                        $yearSection = $assignment->year_level . '-' . ($assignment->enrolled_student ?? 'A');

                        return [
                            'faculty_id'      => $assignment->faculty_id,
                            'subject_id'      => $assignment->subject_id,
                            'classroom_id'    => $classroom->id,
                            'exam_date'       => $specificExamDate,
                            'day'             => $day,
                            'day_name'        => $day,
                            'start_time'      => $slot['start'],
                            'end_time'        => $slot['end'],
                            'exam_type'       => 'Final',
                            'faculty_name'    => $assignment->faculty_name,
                            'course_subject'  => $assignment->subject_name,
                            'course_code'     => $assignment->course_code,
                            'units'           => $assignment->units,
                            'year_section'    => $yearSection,
                            'classroom_name'  => $classroom->room_name ?? $classroom->name ?? 'Room ' . $classroom->id,
                            'year_level'      => $assignment->year_level
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function isExamSlotAvailableForAssignment($exams, $date, $slot, $classroomId, $assignment)
    {
        $assignmentSection = $assignment->year_level . '-' . ($assignment->enrolled_student ?? 'A');
        
        foreach ($exams as $exam) {
            if ($exam['exam_date'] !== $date) continue;
            if (!$this->timesOverlap($slot['start'], $slot['end'], $exam['start_time'], $exam['end_time'])) continue;
            
            if ($exam['classroom_id'] == $classroomId) return false;
            if (($exam['year_section'] ?? null) === $assignmentSection) return false;
        }
        
        return true;
    }

    private function getNextDateForDay($dayName)
    {
        return Carbon::parse("next $dayName")->format('Y-m-d');
    }

    private function getDateForDayInFuture($dayName, $weeks)
    {
        return Carbon::now()->addWeeks($weeks)->next($dayName)->format('Y-m-d');
    }

    /**
     * Save schedules with better error handling and validation
     */
    public function saveSchedule($schedules, $examinations = [])
    {
        try {
            Log::info('SchedulerService: Starting saveSchedule', [
                'schedule_count' => count($schedules),
                'exam_count' => count($examinations)
            ]);

            DB::beginTransaction();

            $savedSchedules = 0;
            $savedExams = 0;
            $errors = [];

            foreach ($schedules as $index => $schedule) {
                try {
                    // Validate required fields
                    if (empty($schedule['faculty_id'])) {
                        $errors[] = "Schedule {$index}: Missing faculty_id";
                        continue;
                    }
                    if (empty($schedule['subject_id'])) {
                        $errors[] = "Schedule {$index}: Missing subject_id";
                        continue;
                    }
                    if (empty($schedule['classroom_id'])) {
                        $errors[] = "Schedule {$index}: Missing classroom_id";
                        continue;
                    }

                    // Ensure time format
                    $startTime = $this->ensureTimeFormat($schedule['start_time']);
                    $endTime = $this->ensureTimeFormat($schedule['end_time']);

                    // Use day name directly
                    $day = $schedule['day_name'] ?? $schedule['day'];

                    // Create schedule record
                    Schedule::create([
                        'faculty_id' => $schedule['faculty_id'],
                        'subject_id' => $schedule['subject_id'],
                        'classroom_id' => $schedule['classroom_id'],
                        'program_id' => $schedule['program_id'] ?? null,
                        'day' => $day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'schedule_date' => $schedule['schedule_date'] ?? null,
                        'class_type' => $schedule['class_type'] ?? 'Lecture',
                        'semester' => $schedule['semester'] ?? null,
                        'year_level' => $schedule['year_level'] ?? null
                    ]);

                    $savedSchedules++;

                } catch (Exception $e) {
                    $error = "Error saving schedule {$index}: " . $e->getMessage();
                    Log::error($error, ['schedule' => $schedule]);
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
                    $endTime = $this->ensureTimeFormat($exam['end_time']);
                    $day = $exam['day_name'] ?? $exam['day'];

                    Examination::create([
                        'faculty_id' => $exam['faculty_id'],
                        'subject_id' => $exam['subject_id'],
                        'classroom_id' => $exam['classroom_id'],
                        'exam_date' => $exam['exam_date'] ?? null,
                        'day' => $day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'exam_type' => $exam['exam_type'] ?? 'Final',
                        'year_section' => $exam['year_section'] ?? null
                    ]);

                    $savedExams++;

                } catch (Exception $e) {
                    $error = "Error saving exam {$index}: " . $e->getMessage();
                    Log::error($error, ['exam' => $exam]);
                    $errors[] = $error;
                }
            }

            // If there were errors but some were saved, still commit
            if (count($errors) > 0 && ($savedSchedules > 0 || $savedExams > 0)) {
                Log::warning('Some schedules had errors but proceeding', ['errors' => $errors]);
            }

            // If nothing was saved and there are errors, rollback
            if ($savedSchedules === 0 && $savedExams === 0 && count($errors) > 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to save schedules. Errors: ' . implode('; ', $errors)
                ];
            }

            DB::commit();

            Log::info("Successfully saved {$savedSchedules} schedules and {$savedExams} examinations");

            $message = "Successfully saved {$savedSchedules} schedules and {$savedExams} examinations";
            if (count($errors) > 0) {
                $message .= " (with " . count($errors) . " errors)";
            }

            return [
                'success' => true,
                'message' => $message,
                'saved_schedules' => $savedSchedules,
                'saved_exams' => $savedExams,
                'errors' => $errors
            ];

        } catch(Exception $e) {
            DB::rollBack();
            Log::error('SchedulerService: Critical error in saveSchedule', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    private function ensureTimeFormat($time)
    {
        if (empty($time)) {
            throw new Exception('Time value cannot be empty');
        }

        $time = trim($time);
        
        if (strlen($time) === 5) {
            return $time . ':00';
        } elseif (strlen($time) === 8) {
            return $time;
        } else {
            throw new Exception("Invalid time format: {$time}");
        }
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
            $examCount = Examination::count();
            
            Schedule::truncate();
            Examination::truncate();
            
            DB::commit();
            
            Log::info("Cleared {$scheduleCount} schedules and {$examCount} examinations");
            
            return [
                'success' => true,
                'message' => "Successfully cleared {$scheduleCount} schedules and {$examCount} examinations"
            ];
        } catch(Exception $e) {
            DB::rollBack();
            Log::error('Error clearing schedules: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error clearing schedules: ' . $e->getMessage()
            ];
        }
    }
}
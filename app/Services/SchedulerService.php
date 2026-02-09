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
     * Convert day name to number
     */
    private function convertDayToNumber($dayName)
    {
        return $this->dayNameToNumber[$dayName] ?? 1;
    }

    /**
     * Generate schedule preview with conflict prevention
     * FIXED: Now uses lecture_units and laboratory_units from faculty_subject table
     */
    public function generateSchedulePreview()
    {
        try {
            Log::info('=== SCHEDULE GENERATION START ===');
            
            // FIXED: Get data from faculty_subject with lecture_units and laboratory_units
            $facultyAssignments = DB::table('faculty_subject')
                ->join('users', 'faculty_subject.faculty_id', '=', 'users.id')
                ->join('subjects', 'faculty_subject.subject_id', '=', 'subjects.id')
                ->select(
                    'faculty_subject.id as assignment_id',
                    'faculty_subject.faculty_id',
                    'faculty_subject.subject_id',
                    'faculty_subject.lecture_units',
                    'faculty_subject.laboratory_units',
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
                'count' => $facultyAssignments->count(),
                'sample_data' => $facultyAssignments->take(2)->toArray()
            ]);
            
            // Log faculty unavailability information
            $facultiesWithUnavailability = DB::table('faculty_unavailabilities')
                ->distinct()
                ->count('faculty_id');
            
            if ($facultiesWithUnavailability > 0) {
                Log::info("Found {$facultiesWithUnavailability} faculties with unavailability restrictions");
            }

            if ($facultyAssignments->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No faculty-subject assignments found. Please assign subjects to faculty first in the Faculty Subjects section.',
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
            
            $subjectDayUsage = [];

            foreach ($facultyAssignments as $assignment) {
                $lectureUnits = (float) ($assignment->lecture_units ?? 0);
                $labUnits = (float) ($assignment->laboratory_units ?? 0);
                $totalUnits = $lectureUnits + $labUnits;

                if ($totalUnits < 1) continue;

                // FIXED: Use lecture_units and laboratory_units directly
                $distribution = $this->getClassDistributionFromFacultySubject($lectureUnits, $labUnits);

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

                        $subjectKey = $assignment->year_level . '_' . $assignment->subject_id;
                        foreach ($sessionSchedules as $session) {
                            if (!isset($subjectDayUsage[$subjectKey])) {
                                $subjectDayUsage[$subjectKey] = [];
                            }
                            $subjectDayUsage[$subjectKey][] = $session['day_name'];
                        }

                        $exam = $this->generateExaminationForAssignment($assignment, $classrooms, $examinations, $totalUnits);
                        if ($exam) $examinations[] = $exam;
                    }
                }

                if (!$scheduled) {
                    // Check if conflict was due to faculty unavailability
                    $unavailabilityReason = '';
                    $facultyUnavailabilities = DB::table('faculty_unavailabilities')
                        ->where('faculty_id', $assignment->faculty_id)
                        ->get();
                    
                    if ($facultyUnavailabilities->isNotEmpty()) {
                        $unavailabilityReason = ' Faculty has ' . $facultyUnavailabilities->count() . ' unavailability slot(s).';
                    }
                    
                    $conflicts[] = [
                        'assignment_id' => $assignment->assignment_id,
                        'faculty' => $assignment->faculty_name,
                        'subject' => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'lecture_units' => $lectureUnits,
                        'laboratory_units' => $labUnits,
                        'total_units' => $totalUnits,
                        'reason' => 'Could not find available time slots after ' . $maxAttempts . ' attempts.' . $unavailabilityReason
                    ];
                }
            }

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

            Log::info('=== SCHEDULE GENERATION COMPLETE ===', [
                'schedules' => count($schedules),
                'exams' => count($examinations),
                'conflicts' => count($conflicts)
            ]);

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
     * NEW METHOD: Get class distribution from faculty_subject lecture and lab units
     * Each lecture unit = 1 hour
     * Each laboratory unit = 3 hours
     */
    private function getClassDistributionFromFacultySubject($lectureUnits, $labUnits)
    {
        $distribution = [];
        
        // Add lecture sessions if lecture units exist
        if ($lectureUnits > 0) {
            $lectureHours = (int)$lectureUnits; // 1 unit = 1 hour
            $distribution[] = ['type' => 'Lecture', 'hours' => $lectureHours];
        }
        
        // Add laboratory sessions if lab units exist
        if ($labUnits > 0) {
            $labHours = (int)($labUnits * 3); // 1 lab unit = 3 hours
            $distribution[] = ['type' => 'Laboratory', 'hours' => $labHours];
        }
        
        return $distribution;
    }

    /**
     * OLD METHOD: Keep for backward compatibility but not used anymore
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
                    ['type' => 'Laboratory', 'hours' => 3]
                ];
                
            case 4: 
                return [
                    ['type' => 'Lecture', 'hours' => 2],
                    ['type' => 'Laboratory', 'hours' => 6]
                ];
                
            case 5: 
                return [['type' => 'Lecture', 'hours' => 5]];
                
            case 6:
                return [['type' => 'Lecture', 'hours' => 6]];
                
            default: 
                return [['type' => 'Lecture', 'hours' => $units]];
        }
    }

    private function scheduleAssignmentSessions($assignment, $distribution, $lectureRooms, $labRooms, $existingSchedules, $subjectDayUsage)
    {
        $sessionSchedules = [];
        $usedDays = [];
        
        $subjectKey = $assignment->year_level . '_' . $assignment->subject_id;
        $existingDays = $subjectDayUsage[$subjectKey] ?? [];

        foreach ($distribution as $session) {
            $hours = $session['hours'];
            $classType = $session['type'];
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
            $usedDays[] = $slot['day_name'];
        }

        return $sessionSchedules;
    }

    private function findAvailableSlotForAssignment($assignment, $hours, $classType, $classrooms, $existingSchedules, $usedDays = [])
    {
        $shuffledDays = $this->daysOfWeek; 
        shuffle($shuffledDays);
        $shuffledClassrooms = $classrooms->shuffle();

        foreach ($shuffledDays as $day) {
            if (in_array($day, $usedDays)) {
                continue;
            }
            
            $availableSlots = $this->getContinuousTimeSlots($hours); 
            shuffle($availableSlots);

            foreach ($availableSlots as $timeSlot) {
                foreach ($shuffledClassrooms as $classroom) {
                    if ($this->isSlotAvailableForAssignment($existingSchedules, $day, $timeSlot['start'], $timeSlot['end'], $classroom->id, $assignment)) {
                        $yearSection = $assignment->year_level . '-A';

                        // Calculate total units for display
                        $totalUnits = (float)($assignment->lecture_units ?? 0) + (float)($assignment->laboratory_units ?? 0);

                        return [
                            'faculty_id'      => $assignment->faculty_id,
                            'subject_id'      => $assignment->subject_id,
                            'classroom_id'    => $classroom->id,
                            'day'             => $day,
                            'day_name'        => $day,
                            'start_time'      => $timeSlot['start'],
                            'end_time'        => $timeSlot['end'],
                            'schedule_date'   => $this->getNextDateForDay($day),
                            'class_type'      => $classType,
                            'faculty_name'    => $assignment->faculty_name,
                            'course_subject'  => $assignment->subject_name,
                            'course_code'     => $assignment->course_code,
                            'units'           => $totalUnits,
                            'lecture_units'   => $assignment->lecture_units ?? 0,
                            'laboratory_units' => $assignment->laboratory_units ?? 0,
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

    /**
     * Check if faculty is unavailable at the given day and time
     */
    private function isFacultyUnavailable($facultyId, $day, $startTime, $endTime)
    {
        try {
            // Convert day name to number if needed
            $dayNumber = is_numeric($day) ? $day : $this->convertDayToNumber($day);
            
            // Query faculty_unavailabilities table
            $unavailabilities = DB::table('faculty_unavailabilities')
                ->where('faculty_id', $facultyId)
                ->where('day', $dayNumber)
                ->get();
            
            // Check if any unavailability conflicts with the proposed time
            foreach ($unavailabilities as $unavailability) {
                if ($this->timesOverlap($startTime, $endTime, $unavailability->start_time, $unavailability->end_time)) {
                    Log::info("Faculty {$facultyId} is unavailable on day {$dayNumber} from {$unavailability->start_time} to {$unavailability->end_time}");
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::warning("Error checking faculty unavailability: " . $e->getMessage());
            // If there's an error checking unavailability, assume available to not block scheduling
            return false;
        }
    }

    private function isSlotAvailableForAssignment($schedules, $day, $startTime, $endTime, $classroomId, $assignment)
    {
        $assignmentSection = $assignment->year_level . '-A';

        // Check if faculty is unavailable at this time
        if ($this->isFacultyUnavailable($assignment->faculty_id, $day, $startTime, $endTime)) {
            return false;
        }

        foreach ($schedules as $schedule) {
            $scheduleDay = $schedule['day_name'] ?? $schedule['day'];
            if ($scheduleDay !== $day) continue;
            
            if (!$this->timesOverlap($startTime, $endTime, $schedule['start_time'], $schedule['end_time'])) continue;

            if ($schedule['classroom_id'] == $classroomId) return false;
            if (isset($schedule['faculty_id']) && $schedule['faculty_id'] == $assignment->faculty_id) return false;
            if (($schedule['year_section'] ?? null) === $assignmentSection) return false;
        }

        return true;
    }

    /**
     * Get faculty unavailability summary for a specific faculty member
     */
    private function getFacultyUnavailabilitySummary($facultyId)
    {
        try {
            $unavailabilities = DB::table('faculty_unavailabilities')
                ->where('faculty_id', $facultyId)
                ->get();
            
            if ($unavailabilities->isEmpty()) {
                return 'No unavailability restrictions';
            }
            
            $summary = [];
            foreach ($unavailabilities as $unavail) {
                $dayName = array_search($unavail->day, $this->dayNameToNumber);
                $summary[] = "{$dayName}: {$unavail->start_time}-{$unavail->end_time}";
            }
            
            return implode(', ', $summary);
        } catch (Exception $e) {
            return 'Unable to fetch unavailability';
        }
    }

    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        return ($start1 < $end2) && ($end1 > $start2);
    }

    private function generateExaminationForAssignment($assignment, $classrooms, $existingExams, $totalUnits)
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
                        $yearSection = $assignment->year_level . '-A';

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
                            'units'           => $totalUnits,
                            'year_section'    => $yearSection,
                            'classroom_name'  => $classroom->room_name ?? $classroom->name ?? 'Room ' . $classroom->id,
                            'year_level'      => $assignment->year_level,
                            'semester'        => $assignment->semester
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function isExamSlotAvailableForAssignment($exams, $date, $slot, $classroomId, $assignment)
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

    private function getNextDateForDay($dayName)
    {
        return Carbon::parse("next $dayName")->format('Y-m-d');
    }

    private function getDateForDayInFuture($dayName, $weeks)
    {
        return Carbon::now()->addWeeks($weeks)->next($dayName)->format('Y-m-d');
    }

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

            // Save regular class schedules to schedules table
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

                    $startTime = $this->ensureTimeFormat($schedule['start_time']);
                    $endTime = $this->ensureTimeFormat($schedule['end_time']);

                    // Convert day name to number
                    $dayName = $schedule['day_name'] ?? $schedule['day'];
                    $dayNumber = $this->convertDayToNumber($dayName);

                    // Build schedule data
                    $data = [
                        'faculty_id' => $schedule['faculty_id'],
                        'subject_id' => $schedule['subject_id'],
                        'classroom_id' => $schedule['classroom_id'],
                        'day' => $dayNumber,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'class_type' => $schedule['class_type'] ?? 'Lecture',
                        'year_level' => $schedule['year_level'] ?? null,
                        'semester' => $schedule['semester'] ?? null,
                        'schedule_date' => $schedule['schedule_date'] ?? null,
                        'section' => $schedule['year_section'] ?? null,
                        'is_active' => true,
                    ];

                    // Add academic year if available
                    $currentYear = now()->year;
                    $nextYear = $currentYear + 1;
                    $data['academic_year'] = "{$currentYear}-{$nextYear}";

                    Schedule::create($data);

                    $savedSchedules++;

                } catch (Exception $e) {
                    $error = "Error saving schedule {$index}: " . $e->getMessage();
                    Log::error($error, ['schedule' => $schedule, 'error' => $e->getTraceAsString()]);
                    $errors[] = $error;
                }
            }

            // Save examinations ONLY to examinations table (NOT to schedules table)
            foreach ($examinations as $index => $exam) {
                try {
                    if (empty($exam['faculty_id']) || empty($exam['subject_id']) || empty($exam['classroom_id'])) {
                        $errors[] = "Exam {$index}: Missing required IDs";
                        continue;
                    }

                    $startTime = $this->ensureTimeFormat($exam['start_time']);
                    $endTime = $this->ensureTimeFormat($exam['end_time']);
                    
                    // Extract day number from exam_date
                    $examDate = Carbon::parse($exam['exam_date']);
                    $dayName = $examDate->format('l'); // Gets full day name (Monday, Tuesday, etc.)
                    $dayNumber = $this->convertDayToNumber($dayName);

                    // Save ONLY to examinations table
                    Examination::create([
                        'faculty_id' => $exam['faculty_id'],
                        'subject_id' => $exam['subject_id'],
                        'classroom_id' => $exam['classroom_id'],
                        'exam_date' => $exam['exam_date'] ?? null,
                        'day' => $dayNumber,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'exam_type' => $exam['exam_type'] ?? 'Final',
                        'year_section' => $exam['year_section'] ?? null,
                        'is_active' => true
                    ]);

                    $savedExams++;

                } catch (Exception $e) {
                    $error = "Error saving exam {$index}: " . $e->getMessage();
                    Log::error($error, ['exam' => $exam, 'trace' => $e->getTraceAsString()]);
                    $errors[] = $error;
                }
            }

            if (count($errors) > 0 && ($savedSchedules > 0 || $savedExams > 0)) {
                Log::warning('Some schedules had errors but proceeding', ['errors' => $errors]);
            }

            if ($savedSchedules === 0 && $savedExams === 0 && count($errors) > 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to save schedules. Errors: ' . implode('; ', array_slice($errors, 0, 3))
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

    public function clearAllExaminations()
    {
        try {
            DB::beginTransaction();
            
            $examCount = Examination::count();
            
            Examination::truncate();
            
            DB::commit();
            
            Log::info("Cleared {$examCount} examinations");
            
            return [
                'success' => true,
                'message' => "Successfully cleared {$examCount} examinations"
            ];
        } catch(Exception $e) {
            DB::rollBack();
            Log::error('Error clearing examinations: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error clearing examinations: ' . $e->getMessage()
            ];
        }
    }
}
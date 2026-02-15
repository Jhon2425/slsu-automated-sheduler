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

    /**
     * Generate examination preview separately from class schedules
     * This should be called independently when you want to generate exams
     * UPDATED: Now properly checks faculty unavailability for examinations
     */
    public function generateExaminationPreview()
    {
        try {
            Log::info('=== EXAMINATION GENERATION START ===');
            
            // Get data from faculty_subject using faculty_code
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
                return [
                    'success' => false,
                    'message' => 'No faculty-subject assignments found.',
                    'examinations' => [],
                    'conflicts' => []
                ];
            }

            $classrooms = Classroom::all();
            if ($classrooms->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No classrooms found.',
                    'examinations' => [],
                    'conflicts' => []
                ];
            }

            $examinations = [];
            $conflicts = [];

            foreach ($facultyAssignments as $assignment) {
                $totalUnits = (float)($assignment->lecture_units ?? 0) + (float)($assignment->laboratory_units ?? 0);
                
                if ($totalUnits < 1) continue;

                $exam = $this->generateExaminationForAssignment($assignment, $classrooms, $examinations, $totalUnits);
                
                if ($exam) {
                    $examinations[] = $exam;
                } else {
                    // Get unavailability details for better conflict reporting
                    $unavailabilitySlots = $this->getFacultyUnavailabilitySlots($assignment->faculty_code);
                    $unavailabilityDetails = '';
                    
                    if (!empty($unavailabilitySlots)) {
                        $slotDescriptions = array_map(fn($slot) => $slot['formatted'], $unavailabilitySlots);
                        $unavailabilityDetails = ' ⚠️ Faculty unavailable: ' . implode(', ', $slotDescriptions);
                    }
                    
                    $conflicts[] = [
                        'assignment_id' => $assignment->assignment_id,
                        'faculty_code' => $assignment->faculty_code,
                        'faculty' => $assignment->faculty_name,
                        'subject' => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'reason' => 'Could not find available examination slot' . $unavailabilityDetails,
                        'unavailability_count' => count($unavailabilitySlots),
                        'unavailable_periods' => $slotDescriptions ?? []
                    ];
                }
            }

            // Format times
            $examinations = array_map(function($exam) {
                $exam['start_time'] = substr($exam['start_time'], 0, 5);
                $exam['end_time'] = substr($exam['end_time'], 0, 5);
                return $exam;
            }, $examinations);

            Log::info('=== EXAMINATION GENERATION COMPLETE ===', [
                'exams' => count($examinations),
                'conflicts' => count($conflicts)
            ]);

            return [
                'success' => true,
                'examinations' => $examinations,
                'conflicts' => $conflicts,
                'message' => count($examinations) . ' examinations generated successfully',
                'stats' => [
                    'total_exams' => count($examinations),
                    'total_conflicts' => count($conflicts)
                ]
            ];

        } catch (Exception $e) {
            Log::error('SchedulerService generateExaminationPreview error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Error generating examinations: ' . $e->getMessage(),
                'examinations' => [],
                'conflicts' => []
            ];
        }
    }

    /**
     * Convert day name to number
     */
    private function convertDayToNumber($dayName)
    {
        return $this->dayNameToNumber[$dayName] ?? 1;
    }

    /**
     * Generate schedule preview with conflict prevention
     * NOTE: This generates REGULAR CLASS SCHEDULES only, NOT examinations
     * Examinations are generated separately and stored in a different table
     * UPDATED: Now uses faculty_code instead of faculty_id
     */
    public function generateSchedulePreview()
    {
        try {
            Log::info('=== SCHEDULE GENERATION START ===');
            
            // UPDATED: Get data from faculty_subject using faculty_code
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
                'count' => $facultyAssignments->count(),
                'sample_data' => $facultyAssignments->take(2)->toArray()
            ]);
            
            // ENHANCED: Log detailed faculty unavailability information
            $facultiesWithUnavailability = DB::table('faculty_unavailabilities')
                ->select('faculty_code')
                ->distinct()
                ->get();
            
            if ($facultiesWithUnavailability->isNotEmpty()) {
                Log::info("=== FACULTY UNAVAILABILITY SUMMARY ===");
                Log::info("Found {$facultiesWithUnavailability->count()} faculties with unavailability restrictions");
                
                // Log details for each faculty with unavailability
                foreach ($facultiesWithUnavailability as $faculty) {
                    $slots = $this->getFacultyUnavailabilitySlots($faculty->faculty_code);
                    Log::info("Faculty {$faculty->faculty_code} unavailable slots:", [
                        'count' => count($slots),
                        'slots' => array_map(fn($s) => $s['formatted'], $slots)
                    ]);
                }
                Log::info("=== END UNAVAILABILITY SUMMARY ===");
            } else {
                Log::info("No faculty unavailability restrictions found");
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

                // Use lecture_units and laboratory_units directly
                // 1 lecture unit = 1 hour
                // 1 laboratory unit = 3 hours
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

                        // NOTE: Examinations are NOT generated here
                        // They should be generated separately using generateExaminationPreview() method
                    }
                }

                if (!$scheduled) {
                    // Get detailed unavailability information
                    $unavailabilitySlots = $this->getFacultyUnavailabilitySlots($assignment->faculty_code);
                    $unavailabilityDetails = '';
                    $slotDescriptions = [];
                    
                    if (!empty($unavailabilitySlots)) {
                        $slotDescriptions = array_map(fn($slot) => $slot['formatted'], $unavailabilitySlots);
                        $unavailabilityDetails = ' ⚠️ Faculty unavailable: ' . implode(', ', $slotDescriptions);
                    }
                    
                    $conflicts[] = [
                        'assignment_id' => $assignment->assignment_id,
                        'faculty_code' => $assignment->faculty_code,
                        'faculty' => $assignment->faculty_name,
                        'subject' => $assignment->subject_name . ' (' . $assignment->course_code . ')',
                        'lecture_units' => $lectureUnits,
                        'laboratory_units' => $labUnits,
                        'total_units' => $totalUnits,
                        'reason' => 'Could not find available time slots after ' . $maxAttempts . ' attempts.' . $unavailabilityDetails,
                        'unavailability_count' => count($unavailabilitySlots),
                        'unavailable_periods' => $slotDescriptions
                    ];
                    
                    Log::warning("❌ CONFLICT: Unable to schedule", [
                        'faculty' => $assignment->faculty_name,
                        'faculty_code' => $assignment->faculty_code,
                        'subject' => $assignment->subject_name,
                        'attempts' => $maxAttempts,
                        'unavailable_slots' => count($unavailabilitySlots),
                        'unavailability_details' => $slotDescriptions
                    ]);
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
                'examinations' => $examinations, // Will be empty - examinations generated separately
                'conflicts' => $conflicts,
                'message' => count($schedules) . ' schedule sessions generated successfully',
                'stats' => [
                    'total_schedules' => count($schedules),
                    'total_exams' => count($examinations), // Will be 0
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
     * Get class distribution from faculty_subject lecture and lab units
     * 1 lecture unit = 1 hour
     * 1 laboratory unit = 3 hours
     */
    private function getClassDistributionFromFacultySubject($lectureUnits, $labUnits)
    {
        $distribution = [];
        
        // Add lecture sessions if lecture units exist
        // 1 lecture unit = 1 hour
        if ($lectureUnits > 0) {
            $lectureHours = (int)$lectureUnits;
            $distribution[] = ['type' => 'Lecture', 'hours' => $lectureHours];
        }
        
        // Add laboratory sessions if lab units exist
        // 1 laboratory unit = 3 hours
        if ($labUnits > 0) {
            $labHours = (int)($labUnits * 3);
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

    /**
     * Find an available time slot for a faculty assignment
     * CRITICAL: Checks faculty unavailability FIRST before attempting to schedule
     * 
     * Process:
     * 1. Pre-load ALL faculty unavailability slots from database
     * 2. For each day and time slot, check if faculty is unavailable FIRST
     * 3. Skip time slots where faculty is unavailable (most efficient)
     * 4. Only then check classroom and other conflicts
     * 5. This ensures faculty is NEVER scheduled during their unavailable times
     */
    private function findAvailableSlotForAssignment($assignment, $hours, $classType, $classrooms, $existingSchedules, $usedDays = [])
    {
        // ============================================================================
        // STEP 1: PRE-LOAD ALL FACULTY UNAVAILABILITY (CRITICAL CHECK)
        // ============================================================================
        // Load ALL unavailability slots for this faculty member BEFORE attempting scheduling
        // This prevents wasted iterations and ensures we never schedule during unavailable times
        $facultyUnavailabilities = DB::table('faculty_unavailabilities')
            ->where('faculty_code', $assignment->faculty_code)
            ->get()
            ->groupBy('day'); // Group by day number for efficient lookup
        
        // Log unavailability information for debugging
        if ($facultyUnavailabilities->isNotEmpty()) {
            $unavailableDays = $facultyUnavailabilities->keys()->map(function($dayNum) {
                return array_search($dayNum, $this->dayNameToNumber) ?: "Day $dayNum";
            })->toArray();
            
            Log::info("🚫 Faculty has unavailability restrictions", [
                'faculty_code' => $assignment->faculty_code,
                'faculty_name' => $assignment->faculty_name,
                'subject' => $assignment->subject_name,
                'unavailable_days' => $unavailableDays,
                'total_restrictions' => $facultyUnavailabilities->flatten()->count()
            ]);
        }

        $shuffledDays = $this->daysOfWeek; 
        shuffle($shuffledDays);
        $shuffledClassrooms = $classrooms->shuffle();

        // ============================================================================
        // STEP 2: ITERATE THROUGH DAYS AND TIME SLOTS
        // ============================================================================
        foreach ($shuffledDays as $day) {
            // Skip if this day was already used for this subject
            if (in_array($day, $usedDays)) {
                Log::debug("⏭️ Day already used for this subject", [
                    'day' => $day,
                    'subject' => $assignment->subject_name
                ]);
                continue;
            }
            
            // Convert day name to number for unavailability checking
            $dayNumber = $this->convertDayToNumber($day);
            
            // ========================================================================
            // CRITICAL: Check if faculty has ANY unavailability on this day
            // ========================================================================
            // If faculty has unavailability restrictions on this day, we need to be careful
            $hasUnavailabilityOnDay = isset($facultyUnavailabilities[$dayNumber]);
            
            if ($hasUnavailabilityOnDay) {
                Log::debug("⚠️ Faculty has unavailability on this day", [
                    'faculty_code' => $assignment->faculty_code,
                    'day' => $day,
                    'day_number' => $dayNumber,
                    'unavailable_slots' => $facultyUnavailabilities[$dayNumber]->count()
                ]);
            }
            
            // Get available time slots for the required duration
            $availableSlots = $this->getContinuousTimeSlots($hours); 
            shuffle($availableSlots);

            foreach ($availableSlots as $timeSlot) {
                // ====================================================================
                // CRITICAL UNAVAILABILITY CHECK - FIRST PRIORITY
                // ====================================================================
                // Check if faculty is unavailable during this specific time slot
                // This is checked BEFORE classroom availability to avoid wasted checks
                
                if ($hasUnavailabilityOnDay) {
                    $isTimeSlotUnavailable = false;
                    $conflictingSlot = null;
                    
                    // Check each unavailability period on this day
                    foreach ($facultyUnavailabilities[$dayNumber] as $unavail) {
                        if ($this->timesOverlap($timeSlot['start'], $timeSlot['end'], $unavail->start_time, $unavail->end_time)) {
                            $isTimeSlotUnavailable = true;
                            $conflictingSlot = $unavail;
                            break;
                        }
                    }
                    
                    // If faculty is unavailable, skip this time slot entirely
                    if ($isTimeSlotUnavailable) {
                        Log::debug("🚫 FACULTY UNAVAILABLE - Skipping time slot", [
                            'faculty_code' => $assignment->faculty_code,
                            'faculty_name' => $assignment->faculty_name,
                            'subject' => $assignment->subject_name,
                            'day' => $day,
                            'requested_time' => "{$timeSlot['start']} - {$timeSlot['end']}",
                            'unavailable_time' => "{$conflictingSlot->start_time} - {$conflictingSlot->end_time}",
                            'reason' => 'Faculty marked as unavailable during this period'
                        ]);
                        continue; // Skip to next time slot
                    }
                }

                // ====================================================================
                // Faculty is available at this time - Now check classroom availability
                // ====================================================================
                foreach ($shuffledClassrooms as $classroom) {
                    if ($this->isSlotAvailableForAssignment($existingSchedules, $day, $timeSlot['start'], $timeSlot['end'], $classroom->id, $assignment)) {
                        $yearSection = $assignment->year_level . '-A';
                        $totalUnits = (float)($assignment->lecture_units ?? 0) + (float)($assignment->laboratory_units ?? 0);

                        Log::info("✅ Successfully scheduled (faculty available)", [
                            'faculty' => $assignment->faculty_name,
                            'faculty_code' => $assignment->faculty_code,
                            'subject' => $assignment->subject_name,
                            'day' => $day,
                            'time' => "{$timeSlot['start']} - {$timeSlot['end']}",
                            'classroom' => $classroom->room_name ?? $classroom->name,
                            'hours' => $hours
                        ]);

                        return [
                            'faculty_id'      => $assignment->faculty_id,
                            'faculty_code'    => $assignment->faculty_code,
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

        // ============================================================================
        // NO AVAILABLE SLOT FOUND
        // ============================================================================
        // Log detailed information about why scheduling failed
        $unavailabilityDetails = $this->getFacultyUnavailabilitySummary($assignment->faculty_code);
        
        Log::warning("❌ SCHEDULING FAILED - No available slots", [
            'faculty' => $assignment->faculty_name,
            'faculty_code' => $assignment->faculty_code,
            'subject' => $assignment->subject_name,
            'hours_needed' => $hours,
            'type' => $classType,
            'unavailability' => $unavailabilityDetails,
            'has_restrictions' => $facultyUnavailabilities->isNotEmpty()
        ]);

        return false;
    }

    /**
     * FIXED: Get continuous time slots for a given number of hours
     * Now properly handles 30-minute intervals
     * Examples:
     * - 1 hour = 2 slots (1 * 2 = 2)
     * - 2 hours = 4 slots (2 * 2 = 4)
     * - 3 hours = 6 slots (3 * 2 = 6)
     */
    private function getContinuousTimeSlots($hours)
    {
        $continuousSlots = [];
        
        // Convert hours to number of 30-minute slots needed
        // 1 hour = 2 slots of 30 minutes
        // 2 hours = 4 slots of 30 minutes
        // 3 hours = 6 slots of 30 minutes
        $slotsNeeded = $hours * 2;
        
        // Generate all possible continuous time slots
        for ($i = 0; $i <= count($this->timeSlots) - $slotsNeeded; $i++) {
            $startTime = $this->timeSlots[$i]['start'];
            
            // Calculate end time by getting the end of the last required slot
            // If we need 4 slots starting at index 0, we want slots [0,1,2,3]
            // So the end time is at index 0 + 4 - 1 = 3, and we take the 'end' time
            $endSlotIndex = $i + $slotsNeeded - 1;
            $endTime = $this->timeSlots[$endSlotIndex]['end'];
            
            $continuousSlots[] = [
                'start' => $startTime,
                'end' => $endTime
            ];
            
            Log::debug("Generated time slot", [
                'hours' => $hours,
                'slots_needed' => $slotsNeeded,
                'start_index' => $i,
                'end_index' => $endSlotIndex,
                'start' => $startTime,
                'end' => $endTime
            ]);
        }
        
        return $continuousSlots;
    }

    /**
     * Check if faculty is unavailable at the given day and time
     * UPDATED: Now uses faculty_code instead of faculty_id
     * IMPROVED: Returns true (unavailable) on errors for safety
     * This is a BACKUP check - the primary check is in findAvailableSlotForAssignment
     */
    private function isFacultyUnavailable($facultyCode, $day, $startTime, $endTime)
    {
        try {
            // Convert day name to number if needed
            $dayNumber = is_numeric($day) ? $day : $this->convertDayToNumber($day);
            
            // Query faculty_unavailabilities table using faculty_code
            $unavailabilities = DB::table('faculty_unavailabilities')
                ->where('faculty_code', $facultyCode)
                ->where('day', $dayNumber)
                ->get();
            
            // If no unavailabilities on this day, faculty is available
            if ($unavailabilities->isEmpty()) {
                return false;
            }
            
            // Check if any unavailability conflicts with the proposed time
            foreach ($unavailabilities as $unavailability) {
                if ($this->timesOverlap($startTime, $endTime, $unavailability->start_time, $unavailability->end_time)) {
                    Log::info("⏰ Faculty unavailability conflict detected (backup check)", [
                        'faculty_code' => $facultyCode,
                        'day_number' => $dayNumber,
                        'day_name' => $day,
                        'requested_time' => "{$startTime} - {$endTime}",
                        'unavailable_time' => "{$unavailability->start_time} - {$unavailability->end_time}",
                        'overlap' => true
                    ]);
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error("CRITICAL: Error checking faculty unavailability - treating as UNAVAILABLE for safety", [
                'faculty_code' => $facultyCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // SAFETY: Return true (unavailable) on error to prevent scheduling
            return true;
        }
    }

    /**
     * Get all unavailability slots for a faculty member
     * Useful for pre-checking and conflict reporting
     */
    private function getFacultyUnavailabilitySlots($facultyCode)
    {
        try {
            $unavailabilities = DB::table('faculty_unavailabilities')
                ->where('faculty_code', $facultyCode)
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();
            
            $slots = [];
            foreach ($unavailabilities as $unavail) {
                $dayName = array_search($unavail->day, $this->dayNameToNumber) ?: "Day {$unavail->day}";
                $slots[] = [
                    'day' => $unavail->day,
                    'day_name' => $dayName,
                    'start_time' => $unavail->start_time,
                    'end_time' => $unavail->end_time,
                    'formatted' => "{$dayName} {$unavail->start_time}-{$unavail->end_time}"
                ];
            }
            
            return $slots;
        } catch (Exception $e) {
            Log::warning("Error fetching faculty unavailability slots: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ENHANCED: Check if a time slot is available for scheduling
     * This method now includes comprehensive conflict detection with detailed logging:
     * 
     * 1. Faculty Unavailability Check - Prevents scheduling during faculty's unavailable times
     * 2. Classroom Conflict Check - Ensures no two classes use the same classroom at the same time on the same day
     * 3. Faculty Conflict Check - Prevents double-booking faculty members
     * 4. Section Conflict Check - Prevents students from having overlapping classes
     * 
     * @param array $schedules - Existing schedules to check against
     * @param string $day - Day of the week
     * @param string $startTime - Start time of the proposed schedule
     * @param string $endTime - End time of the proposed schedule
     * @param int $classroomId - Classroom ID to check
     * @param object $assignment - Faculty assignment object
     * @return bool - True if slot is available, false if there's a conflict
     */
    private function isSlotAvailableForAssignment($schedules, $day, $startTime, $endTime, $classroomId, $assignment)
    {
        $assignmentSection = $assignment->year_level . '-A';

        // ===================================================================
        // CHECK 1: Faculty Unavailability (BACKUP CHECK)
        // ===================================================================
        // Note: Primary unavailability check is in findAvailableSlotForAssignment
        // This is a secondary safeguard
        if ($this->isFacultyUnavailable($assignment->faculty_code, $day, $startTime, $endTime)) {
            Log::debug("❌ Faculty unavailable (backup check)", [
                'faculty_code' => $assignment->faculty_code,
                'faculty_name' => $assignment->faculty_name,
                'day' => $day,
                'time' => "$startTime - $endTime"
            ]);
            return false;
        }

        // ===================================================================
        // CHECK 2-4: Iterate through existing schedules to detect conflicts
        // ===================================================================
        foreach ($schedules as $schedule) {
            $scheduleDay = $schedule['day_name'] ?? $schedule['day'];
            
            // Skip if different day - no conflict possible
            if ($scheduleDay !== $day) {
                continue;
            }
            
            // Skip if times don't overlap - no conflict possible
            // Two time ranges overlap if one starts before the other ends
            if (!$this->timesOverlap($startTime, $endTime, $schedule['start_time'], $schedule['end_time'])) {
                continue;
            }

            // At this point, we have overlapping times on the same day
            // Now check for specific types of conflicts:

            // ===================================================================
            // CHECK 2: Classroom Conflict (CRITICAL)
            // ===================================================================
            // A classroom cannot be used by two different classes at the same time
            // Example: Room 101 cannot host Math at 9:00-10:00 and English at 9:30-10:30
            if ($schedule['classroom_id'] == $classroomId) {
                Log::warning("❌ CLASSROOM CONFLICT DETECTED", [
                    'classroom_id' => $classroomId,
                    'day' => $day,
                    'requested_subject' => $assignment->subject_name,
                    'requested_faculty' => $assignment->faculty_name,
                    'requested_time' => "$startTime - $endTime",
                    'existing_subject' => $schedule['course_subject'] ?? 'N/A',
                    'existing_faculty' => $schedule['faculty_name'] ?? 'N/A',
                    'existing_time' => "{$schedule['start_time']} - {$schedule['end_time']}"
                ]);
                return false;
            }
            
            // ===================================================================
            // CHECK 3: Faculty Conflict
            // ===================================================================
            // A faculty member cannot teach two different classes at the same time
            // Example: Prof. Smith cannot teach Math and English simultaneously
            if (isset($schedule['faculty_code']) && $schedule['faculty_code'] == $assignment->faculty_code) {
                Log::debug("❌ Faculty double-booking detected", [
                    'faculty_code' => $assignment->faculty_code,
                    'faculty_name' => $assignment->faculty_name,
                    'day' => $day,
                    'requested_subject' => $assignment->subject_name,
                    'requested_time' => "$startTime - $endTime",
                    'existing_subject' => $schedule['course_subject'] ?? 'N/A',
                    'existing_time' => "{$schedule['start_time']} - {$schedule['end_time']}"
                ]);
                return false;
            }
            
            // ===================================================================
            // CHECK 4: Section/Student Conflict
            // ===================================================================
            // Students in the same section cannot have overlapping classes
            // Example: Year 1-A students cannot have Math and English at the same time
            if (($schedule['year_section'] ?? null) === $assignmentSection) {
                Log::debug("❌ Section conflict detected", [
                    'section' => $assignmentSection,
                    'day' => $day,
                    'requested_subject' => $assignment->subject_name,
                    'requested_time' => "$startTime - $endTime",
                    'existing_subject' => $schedule['course_subject'] ?? 'N/A',
                    'existing_time' => "{$schedule['start_time']} - {$schedule['end_time']}"
                ]);
                return false;
            }
        }

        // ===================================================================
        // ALL CHECKS PASSED
        // ===================================================================
        // No conflicts detected - the time slot is available for scheduling
        Log::debug("✅ Slot available", [
            'subject' => $assignment->subject_name,
            'faculty' => $assignment->faculty_name,
            'classroom' => $classroomId,
            'day' => $day,
            'time' => "$startTime - $endTime"
        ]);
        
        return true;
    }

    /**
     * Get faculty unavailability summary for a specific faculty member
     * UPDATED: Now uses faculty_code instead of faculty_id
     */
    private function getFacultyUnavailabilitySummary($facultyCode)
    {
        try {
            $unavailabilities = DB::table('faculty_unavailabilities')
                ->where('faculty_code', $facultyCode)
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

    /**
     * Check if two time ranges overlap
     * Returns true if there is any overlap between the two time periods
     */
    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        return ($start1 < $end2) && ($end1 > $start2);
    }

    /**
     * Generate examination for a faculty assignment
     * UPDATED: Now checks faculty unavailability for examinations
     * UPDATED: Examinations are now 1 hour only (not 2 hours)
     * NOTE: Examinations are separate from regular class schedules
     */
    private function generateExaminationForAssignment($assignment, $classrooms, $existingExams, $totalUnits)
    {
        // ============================================================================
        // STEP 1: PRE-LOAD FACULTY UNAVAILABILITY FOR EXAM SCHEDULING
        // ============================================================================
        $facultyUnavailabilities = DB::table('faculty_unavailabilities')
            ->where('faculty_code', $assignment->faculty_code)
            ->get()
            ->groupBy('day');
        
        if ($facultyUnavailabilities->isNotEmpty()) {
            $unavailableDays = $facultyUnavailabilities->keys()->map(function($dayNum) {
                return array_search($dayNum, $this->dayNameToNumber) ?: "Day $dayNum";
            })->toArray();
            
            Log::info("🚫 Faculty has unavailability restrictions (EXAM scheduling)", [
                'faculty_code' => $assignment->faculty_code,
                'faculty_name' => $assignment->faculty_name,
                'subject' => $assignment->subject_name,
                'unavailable_days' => $unavailableDays,
                'total_restrictions' => $facultyUnavailabilities->flatten()->count()
            ]);
        }
        
        $weeksAhead = rand(8, 10);
        $shuffledDays = $this->daysOfWeek; 
        shuffle($shuffledDays);

        // All examination slots are now 1 hour long
        $examTimeSlots = [
            ['start' => '08:00:00', 'end' => '09:00:00'],
            ['start' => '09:00:00', 'end' => '10:00:00'],
            ['start' => '10:00:00', 'end' => '11:00:00'],
            ['start' => '11:00:00', 'end' => '12:00:00'],
            ['start' => '13:00:00', 'end' => '14:00:00'],
            ['start' => '14:00:00', 'end' => '15:00:00'],
            ['start' => '15:00:00', 'end' => '16:00:00'],
            ['start' => '16:00:00', 'end' => '17:00:00']
        ]; 
        shuffle($examTimeSlots);

        foreach ($shuffledDays as $day) {
            $dayNumber = $this->convertDayToNumber($day);
            
            // ========================================================================
            // CHECK: Does faculty have unavailability on this day?
            // ========================================================================
            $hasUnavailabilityOnDay = isset($facultyUnavailabilities[$dayNumber]);
            
            if ($hasUnavailabilityOnDay) {
                Log::debug("⚠️ Faculty has unavailability on this day (EXAM)", [
                    'faculty_code' => $assignment->faculty_code,
                    'day' => $day,
                    'day_number' => $dayNumber,
                    'unavailable_slots' => $facultyUnavailabilities[$dayNumber]->count()
                ]);
            }
            
            foreach ($examTimeSlots as $slot) {
                // ====================================================================
                // CRITICAL: Check faculty unavailability BEFORE scheduling exam
                // ====================================================================
                if ($hasUnavailabilityOnDay) {
                    $isTimeSlotUnavailable = false;
                    $conflictingSlot = null;
                    
                    foreach ($facultyUnavailabilities[$dayNumber] as $unavail) {
                        if ($this->timesOverlap($slot['start'], $slot['end'], $unavail->start_time, $unavail->end_time)) {
                            $isTimeSlotUnavailable = true;
                            $conflictingSlot = $unavail;
                            break;
                        }
                    }
                    
                    if ($isTimeSlotUnavailable) {
                        Log::debug("🚫 EXAM: Faculty unavailable - Skipping time slot", [
                            'faculty_code' => $assignment->faculty_code,
                            'faculty_name' => $assignment->faculty_name,
                            'subject' => $assignment->subject_name,
                            'day' => $day,
                            'exam_time' => "{$slot['start']} - {$slot['end']}",
                            'unavailable_time' => "{$conflictingSlot->start_time} - {$conflictingSlot->end_time}",
                            'reason' => 'Faculty marked as unavailable during this exam period'
                        ]);
                        continue; // Skip this time slot
                    }
                }
                
                // ====================================================================
                // Faculty is available - Now check classroom availability
                // ====================================================================
                foreach ($classrooms->shuffle() as $classroom) {
                    $specificExamDate = $this->getDateForDayInFuture($day, $weeksAhead);

                    if ($this->isExamSlotAvailableForAssignment($existingExams, $specificExamDate, $slot, $classroom->id, $assignment)) {
                        $yearSection = $assignment->year_level . '-A';

                        Log::info("✅ EXAM scheduled successfully (faculty available)", [
                            'faculty' => $assignment->faculty_name,
                            'faculty_code' => $assignment->faculty_code,
                            'subject' => $assignment->subject_name,
                            'day' => $day,
                            'exam_date' => $specificExamDate,
                            'time' => "{$slot['start']} - {$slot['end']}",
                            'classroom' => $classroom->room_name ?? $classroom->name
                        ]);

                        return [
                            'faculty_id'      => $assignment->faculty_id,
                            'faculty_code'    => $assignment->faculty_code,
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

        // ============================================================================
        // NO AVAILABLE EXAM SLOT FOUND
        // ============================================================================
        $unavailabilityDetails = $this->getFacultyUnavailabilitySummary($assignment->faculty_code);
        
        Log::warning("❌ EXAM SCHEDULING FAILED - No available slots", [
            'faculty' => $assignment->faculty_name,
            'faculty_code' => $assignment->faculty_code,
            'subject' => $assignment->subject_name,
            'unavailability' => $unavailabilityDetails,
            'has_restrictions' => $facultyUnavailabilities->isNotEmpty()
        ]);

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

    /**
     * Save schedules and/or examinations to database
     * NOTE: Schedules go to 'schedules' table, examinations go to 'examinations' table
     * Can be called with only schedules, only examinations, or both
     * FIXED: Now includes faculty_code when saving
     */
    public function saveSchedule($schedules = [], $examinations = [])
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

            // Save regular class schedules to 'schedules' table ONLY
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

                    // Build schedule data - FIXED: Now includes faculty_code
                    $data = [
                        'faculty_id' => $schedule['faculty_id'],
                        'faculty_code' => $schedule['faculty_code'] ?? null,  // ✓ FIXED: Added faculty_code
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

            // Save examinations to 'examinations' table ONLY (NOT to schedules table)
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

                    // Save ONLY to examinations table - FIXED: Now includes faculty_code
                    Examination::create([
                        'faculty_id' => $exam['faculty_id'],
                        'faculty_code' => $exam['faculty_code'] ?? null,  // ✓ FIXED: Added faculty_code
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

    /**
     * Save only examinations to database
     * Convenience method for saving exam data without schedules
     */
    public function saveExaminations($examinations)
    {
        return $this->saveSchedule([], $examinations);
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

    /**
     * Test method to verify faculty unavailability is being respected
     * Call this from a controller to verify the system is working
     * 
     * Usage in controller:
     * $result = app(SchedulerService::class)->testFacultyUnavailability('FAC001');
     * return response()->json($result);
     */
    public function testFacultyUnavailability($facultyCode)
    {
        $unavailabilities = DB::table('faculty_unavailabilities')
            ->where('faculty_code', $facultyCode)
            ->get();
        
        if ($unavailabilities->isEmpty()) {
            return [
                'faculty_code' => $facultyCode,
                'has_unavailability' => false,
                'message' => 'No unavailability restrictions found'
            ];
        }
        
        $details = [];
        foreach ($unavailabilities as $unavail) {
            $dayName = array_search($unavail->day, $this->dayNameToNumber);
            $details[] = [
                'day' => $dayName,
                'day_number' => $unavail->day,
                'start_time' => $unavail->start_time,
                'end_time' => $unavail->end_time
            ];
        }
        
        return [
            'faculty_code' => $facultyCode,
            'has_unavailability' => true,
            'count' => count($unavailabilities),
            'details' => $details,
            'message' => "Found {count($unavailabilities)} unavailability restrictions for faculty {$facultyCode}"
        ];
    }
}
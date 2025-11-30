<?php

// app/Services/SchedulerService.php
namespace App\Services;

use App\Models\Faculty;
use App\Models\Classroom;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchedulerService
{
    private $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    private $timeSlots = [
        ['start' => '07:00', 'end' => '08:00'],
        ['start' => '08:00', 'end' => '09:00'],
        ['start' => '09:00', 'end' => '10:00'],
        ['start' => '10:00', 'end' => '11:00'],
        ['start' => '11:00', 'end' => '12:00'],
        ['start' => '13:00', 'end' => '14:00'],
        ['start' => '14:00', 'end' => '15:00'],
        ['start' => '15:00', 'end' => '16:00'],
        ['start' => '16:00', 'end' => '17:00'],
        ['start' => '17:00', 'end' => '18:00'],
    ];

    /**
     * Generate complete schedule for all faculties
     * Using Constraint-based Greedy Algorithm with Conflict Resolution
     */
    public function generateSchedule()
    {
        DB::beginTransaction();
        
        try {
            // Clear existing schedules
            Schedule::truncate();

            // Get all faculties sorted by priority
            $faculties = Faculty::orderByDesc('no_of_students')
                               ->orderByDesc('no_of_hours')
                               ->get();

            foreach ($faculties as $faculty) {
                $scheduled = $this->scheduleFaculty($faculty);
                
                if (!$scheduled) {
                    throw new \Exception("Unable to schedule: {$faculty->name} - {$faculty->course_subject}");
                }
            }

            DB::commit();
            return ['success' => true, 'message' => 'Schedule generated successfully!'];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Schedule a single faculty using greedy approach
     */
    private function scheduleFaculty(Faculty $faculty)
    {
        $hoursNeeded = $faculty->no_of_hours;
        $hoursScheduled = 0;
        $attempts = 0;
        $maxAttempts = 100;

        // Get suitable classrooms
        $classrooms = $this->getSuitableClassrooms($faculty);
        
        if ($classrooms->isEmpty()) {
            return false;
        }

        while ($hoursScheduled < $hoursNeeded && $attempts < $maxAttempts) {
            $attempts++;

            // Randomly select day and time slot
            $day = $this->days[array_rand($this->days)];
            $timeSlot = $this->timeSlots[array_rand($this->timeSlots)];
            
            // Try to find available classroom
            foreach ($classrooms->shuffle() as $classroom) {
                if ($this->canSchedule($faculty, $classroom, $day, $timeSlot['start'], $timeSlot['end'])) {
                    
                    // Calculate duration
                    $duration = $this->calculateDuration($timeSlot['start'], $timeSlot['end']);
                    
                    // Create schedule
                    Schedule::create([
                        'faculty_id' => $faculty->id,
                        'classroom_id' => $classroom->id,
                        'day' => $day,
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'schedule_date' => $this->getNextDate($day),
                    ]);

                    $hoursScheduled += $duration;
                    break;
                }
            }
        }

        return $hoursScheduled >= $hoursNeeded;
    }

    /**
     * Check if schedule can be assigned (no conflicts)
     */
    private function canSchedule($faculty, $classroom, $day, $startTime, $endTime)
    {
        // Check 1: Room availability
        $roomConflict = Schedule::where('classroom_id', $classroom->id)
            ->where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->exists();

        if ($roomConflict) return false;

        // Check 2: Faculty time conflict
        $facultyConflict = Schedule::where('faculty_id', $faculty->id)
            ->where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->exists();

        if ($facultyConflict) return false;

        // Check 3: Year/Section conflict
        $sectionConflict = Schedule::whereHas('faculty', function ($query) use ($faculty) {
                $query->where('year_section', $faculty->year_section);
            })
            ->where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->exists();

        if ($sectionConflict) return false;

        return true;
    }

    /**
     * Get suitable classrooms based on faculty requirements
     */
    private function getSuitableClassrooms(Faculty $faculty)
    {
        return Classroom::where('capacity', '>=', $faculty->no_of_students)
            ->where(function ($query) use ($faculty) {
                $query->where('type', $faculty->action_type)
                      ->orWhere('type', 'Both');
            })
            ->get();
    }

    /**
     * Calculate duration in hours
     */
    private function calculateDuration($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        return $start->diffInHours($end);
    }

    /**
     * Get next occurrence of the given day
     */
    private function getNextDate($day)
    {
        $daysMap = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];

        return Carbon::now()->next($daysMap[$day]);
    }
}
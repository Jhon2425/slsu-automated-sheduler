<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\FacultySubject;
use App\Models\Subject;
use App\Models\Program;
use App\Services\SchedulerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    protected $schedulerService;

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    /**
     * Display all schedules with proper day handling
     */
    public function index()
    {
        $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->paginate(1000);

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show schedule generation page with faculty data
     */
    public function create()
    {
        $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');
        
        $faculties = User::where('role_id', $facultyRoleId)
            ->with(['faculty.facultySubjects.subject'])
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $stats = [
            'total_faculty' => $faculties->count(),
            'total_assignments' => FacultySubject::count(),
            'unique_subjects' => Subject::whereHas('facultySubjects')->count(),
            'programs_count' => Program::count(),
        ];

        return view('admin.schedules.generate', compact('faculties', 'stats'));
    }

    /**
     * Show a single schedule (for modal view)
     */
    public function show($id)
    {
        try {
            $schedule = Schedule::with(['subject', 'faculty', 'classroom'])->findOrFail($id);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'schedule' => $schedule
                ]);
            }
            
            return view('admin.schedules.show', compact('schedule'));
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }
            
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Schedule not found');
        }
    }

    /**
     * Generate preview using the SchedulerService
     */
    public function generatePreview(Request $request)
    {
        try {
            Log::info('Generate Preview Request', [
                'schedule_type' => $request->input('schedule_type')
            ]);

            $result = $this->schedulerService->generateSchedulePreview();

            Log::info('Schedule Preview Generated', [
                'success' => $result['success'],
                'schedule_count' => count($result['schedules']),
                'exam_count' => count($result['examinations']),
                'conflict_count' => count($result['conflicts'])
            ]);

            // Store in session for review
            if ($result['success']) {
                session([
                    'schedule_preview' => $result['schedules'],
                    'examination_preview' => $result['examinations'],
                    'schedule_conflicts' => $result['conflicts']
                ]);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error in generatePreview controller', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating preview: ' . $e->getMessage(),
                'schedules' => [],
                'examinations' => [],
                'conflicts' => []
            ], 500);
        }
    }

    /**
     * Review generated schedule before saving
     */
    public function review()
    {
        $schedules = session('schedule_preview', []);
        $examinations = session('examination_preview', []);
        $conflicts = session('schedule_conflicts', []);

        if (empty($schedules)) {
            return redirect()->route('admin.schedules.create')
                ->with('error', 'No schedule preview available. Please generate a schedule first.');
        }

        // Group schedules by day for better display
        $schedulesByDay = collect($schedules)->groupBy('day_name');
        $examinationsByDate = collect($examinations)->groupBy('exam_date');

        return view('admin.schedules.review', compact(
            'schedules',
            'examinations',
            'conflicts',
            'schedulesByDay',
            'examinationsByDate'
        ));
    }

    /**
     * Confirm and save schedules from preview/review
     */
    public function confirm(Request $request)
    {
        try {
            Log::info('=== CONFIRM SCHEDULE START ===');
            Log::info('Request Method: ' . $request->method());
            
            // Check if we have session data (from review page)
            $sessionSchedules = session('schedule_preview', []);
            $sessionExams = session('examination_preview', []);
            
            // Get from request (from AJAX) or session
            $schedules = $request->input('schedules', $sessionSchedules);
            $examinations = $request->input('examinations', $sessionExams);
            $scheduleType = $request->input('schedule_type', 'regular');

            Log::info('Parsed Data:', [
                'schedule_type' => $scheduleType,
                'schedule_count' => count($schedules),
                'exam_count' => count($examinations)
            ]);

            // Validate data
            if (empty($schedules) && empty($examinations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules provided'
                ], 400);
            }

            Log::info('Calling schedulerService->saveSchedule...');
            $result = $this->schedulerService->saveSchedule($schedules, $examinations);

            // Clear session data on success
            if ($result['success']) {
                session()->forget(['schedule_preview', 'examination_preview', 'schedule_conflicts']);
            }

            Log::info('Schedule Save Result:', $result);
            Log::info('=== CONFIRM SCHEDULE END ===');

            return response()->json($result);

        } catch (\Throwable $e) {
            Log::error('=== FATAL ERROR IN CONFIRM ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Critical error: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Cancel preview and go back
     */
    public function cancel()
    {
        session()->forget(['schedule_preview', 'examination_preview', 'schedule_conflicts']);
        
        return redirect()->route('admin.schedules.create')
            ->with('info', 'Schedule preview cancelled.');
    }

    /**
     * View previous schedules history
     */
    public function viewPrevious()
    {
        try {
            $previousSchedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->where('is_active', false)
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            return view('admin.schedules.previous', compact('previousSchedules'));

        } catch (\Exception $e) {
            Log::error('Error viewing previous schedules', [
                'message' => $e->getMessage()
            ]);

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error loading previous schedules: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar data for FullCalendar integration
     */
    public function getCalendarData(Request $request)
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->where('is_active', true)
                ->get();

            $events = $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->subject->name ?? 'N/A',
                    'start' => $this->getNextOccurrence($schedule->day, $schedule->start_time),
                    'end' => $this->getNextOccurrence($schedule->day, $schedule->end_time),
                    'daysOfWeek' => [$this->getDayNumber($schedule->day)],
                    'startTime' => $schedule->start_time,
                    'endTime' => $schedule->end_time,
                    'extendedProps' => [
                        'faculty' => $schedule->faculty->name ?? 'N/A',
                        'classroom' => $schedule->classroom->name ?? 'N/A',
                        'type' => $schedule->type ?? 'regular'
                    ]
                ];
            });

            return response()->json($events);

        } catch (\Exception $e) {
            Log::error('Error getting calendar data', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading calendar data'
            ], 500);
        }
    }

    /**
     * Print schedule view - ENHANCED VERSION
     */
    public function printSchedule()
    {
        try {
            return view('admin.schedules.print-ajax');

        } catch (\Exception $e) {
            Log::error('Error loading print view', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error loading print view: ' . $e->getMessage());
        }
    }

    /**
     * Get schedule data as JSON for AJAX requests
     */
    public function getScheduleData(Request $request)
    {
        try {
            Log::info('=== SCHEDULE DATA API CALLED ===');

            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->orderBy('start_time')
                ->get();

            Log::info('Schedules fetched', ['count' => $schedules->count()]);

            $colors = [
                'pink', 'blue', 'green', 'yellow', 'purple', 'red', 
                'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia'
            ];
            
            $subjectColors = [];
            $colorIndex = 0;
            
            foreach($schedules as $schedule) {
                if($schedule->subject_id && !isset($subjectColors[$schedule->subject_id])) {
                    $subjectColors[$schedule->subject_id] = $colors[$colorIndex % count($colors)];
                    $colorIndex++;
                }
            }

            $response = [
                'success' => true,
                'schedules' => [
                    'data' => $schedules
                ],
                'subjectColors' => $subjectColors
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('=== ERROR IN SCHEDULE DATA API ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading schedule data: ' . $e->getMessage(),
                'schedules' => ['data' => []],
                'subjectColors' => []
            ], 500);
        }
    }

    /**
     * Download schedules as PDF - ACCURATE TIME PLOTTING FIX
     */
    public function downloadPDF(Request $request)
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->orderBy('start_time')
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            // Generate 30-minute interval time slots from 7:00 AM to 7:30 PM
            $timeSlots = [];
            for ($hour = 7; $hour <= 19; $hour++) {
                $timeSlots[] = sprintf('%02d:00', $hour);
                if ($hour < 19) {
                    $timeSlots[] = sprintf('%02d:30', $hour);
                }
            }
            
            Log::info('Time slots generated', ['slots' => $timeSlots]);
            
            // Initialize the grid
            $schedulesByDayAndTime = [];
            foreach($days as $day) {
                $schedulesByDayAndTime[$day] = [];
                foreach($timeSlots as $time) {
                    $schedulesByDayAndTime[$day][$time] = [];
                }
            }
            
            $occupiedCells = [];
            
            // Assign consistent colors to subjects
            $colors = [
                'pink', 'blue', 'green', 'yellow', 'purple', 'red', 
                'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia'
            ];
            
            $subjectColors = [];
            $colorIndex = 0;
            
            foreach($schedules as $schedule) {
                if($schedule->subject_id && !isset($subjectColors[$schedule->subject_id])) {
                    $subjectColors[$schedule->subject_id] = $colors[$colorIndex % count($colors)];
                    $colorIndex++;
                }
            }
            
            // Plot schedules accurately on the time grid
            foreach($schedules as $schedule) {
                $day = $schedule->day;
                
                if(!in_array($day, $days)) continue;
                
                // Parse times accurately (handle both HH:MM:SS and HH:MM formats)
                $startTime = substr($schedule->start_time, 0, 5); // HH:MM
                $endTime = substr($schedule->end_time, 0, 5);     // HH:MM
                
                // Convert to minutes for accurate calculation
                list($startHour, $startMin) = explode(':', $startTime);
                list($endHour, $endMin) = explode(':', $endTime);
                
                $startMinutes = (int)$startHour * 60 + (int)$startMin;
                $endMinutes = (int)$endHour * 60 + (int)$endMin;
                $durationMinutes = $endMinutes - $startMinutes;
                
                Log::info('Processing schedule', [
                    'subject' => $schedule->subject->name ?? 'N/A',
                    'day' => $day,
                    'start' => $startTime,
                    'end' => $endTime,
                    'startMinutes' => $startMinutes,
                    'endMinutes' => $endMinutes,
                    'duration' => $durationMinutes
                ]);
                
                // Find EXACT matching time slot (must match start time exactly)
                $matchedSlot = null;
                
                foreach($timeSlots as $slot) {
                    if ($slot === $startTime) {
                        $matchedSlot = $slot;
                        break;
                    }
                }
                
                // If no exact match, find the nearest slot that's <= start time
                if (!$matchedSlot) {
                    $minDiff = PHP_INT_MAX;
                    foreach($timeSlots as $slot) {
                        list($slotHour, $slotMin) = explode(':', $slot);
                        $slotMinutes = (int)$slotHour * 60 + (int)$slotMin;
                        
                        // Only consider slots that are at or before the start time
                        if ($slotMinutes <= $startMinutes) {
                            $diff = $startMinutes - $slotMinutes;
                            if ($diff < $minDiff) {
                                $minDiff = $diff;
                                $matchedSlot = $slot;
                            }
                        }
                    }
                }
                
                // Fallback to first slot if nothing matched
                if (!$matchedSlot && count($timeSlots) > 0) {
                    $matchedSlot = $timeSlots[0];
                }
                
                if ($matchedSlot && isset($schedulesByDayAndTime[$day][$matchedSlot])) {
                    // Calculate rowspan: each slot is 30 minutes
                    // Example: 2 hours (120 min) = 4 cells (120/30 = 4)
                    // Example: 1.5 hours (90 min) = 3 cells (90/30 = 3)
                    // Example: 1 hour (60 min) = 2 cells (60/30 = 2)
                    $rowspan = (int)ceil($durationMinutes / 30);
                    
                    // Ensure minimum rowspan of 1
                    $rowspan = max(1, $rowspan);
                    
                    $schedule->calculated_rowspan = $rowspan;
                    $schedule->assigned_color = $subjectColors[$schedule->subject_id] ?? 'gray';
                    
                    $schedulesByDayAndTime[$day][$matchedSlot][] = $schedule;
                    
                    // Mark occupied cells for rowspan
                    // The first cell contains the schedule data, subsequent cells are occupied/blocked
                    // Example: If rowspan=4 (2 hours), we occupy cells at index+1, index+2, index+3
                    $slotIndex = array_search($matchedSlot, $timeSlots);
                    
                    if ($slotIndex !== false) {
                        if(!isset($occupiedCells[$day])) {
                            $occupiedCells[$day] = [];
                        }
                        
                        // Mark cells 1 through (rowspan-1) as occupied
                        // For a 2-hour class (rowspan=4), mark 3 additional cells after the first one
                        for($i = 1; $i < $rowspan; $i++) {
                            $nextIndex = $slotIndex + $i;
                            if($nextIndex < count($timeSlots)) {
                                $nextSlot = $timeSlots[$nextIndex];
                                $occupiedCells[$day][$nextSlot] = true;
                            }
                        }
                    }
                    
                    Log::info('✓ Successfully plotted schedule', [
                        'subject' => $schedule->subject->name ?? 'N/A',
                        'day' => $day,
                        'matched_slot' => $matchedSlot,
                        'rowspan' => $rowspan,
                        'duration_mins' => $durationMinutes,
                        'color' => $schedule->assigned_color,
                        'slot_index' => $slotIndex,
                        'occupied_cell_count' => $rowspan - 1,
                        'occupied_indices' => range($slotIndex + 1, min($slotIndex + $rowspan - 1, count($timeSlots) - 1))
                    ]);
                } else {
                    Log::warning('Failed to plot schedule', [
                        'subject' => $schedule->subject->name ?? 'N/A',
                        'day' => $day,
                        'start' => $startTime,
                        'matched_slot' => $matchedSlot
                    ]);
                }
            }

            return view('admin.schedules.print', compact(
                'schedules', 
                'days', 
                'timeSlots', 
                'schedulesByDayAndTime', 
                'occupiedCells',
                'subjectColors'
            ));

        } catch (\Exception $e) {
            Log::error('Error generating PDF view', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download schedules as Excel
     */
    public function downloadExcel()
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->orderBy('start_time')
                ->get();

            return redirect()->route('admin.schedules.index')
                ->with('info', 'Excel download functionality to be implemented. Please install maatwebsite/excel');

        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }

    /**
     * Clear all schedules
     */
    public function clearAllSchedules()
    {
        try {
            $result = $this->schedulerService->clearAllSchedules();

            if (request()->ajax()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return redirect()->route('admin.schedules.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('admin.schedules.index')
                    ->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Error clearing schedules', [
                'message' => $e->getMessage()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error clearing schedules: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error clearing schedules: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Get day number for FullCalendar
     */
    private function getDayNumber($day)
    {
        $days = [
            'Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2,
            'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6
        ];
        return $days[$day] ?? 1;
    }

    /**
     * Helper: Get next occurrence of a day with time
     */
    private function getNextOccurrence($day, $time)
    {
        $dayNumber = $this->getDayNumber($day);
        $now = now();
        $currentDay = $now->dayOfWeek;
        $daysUntil = ($dayNumber - $currentDay + 7) % 7;
        if ($daysUntil === 0 && $now->format('H:i:s') > $time) {
            $daysUntil = 7;
        }
        return $now->addDays($daysUntil)->format('Y-m-d') . 'T' . $time;
    }
}
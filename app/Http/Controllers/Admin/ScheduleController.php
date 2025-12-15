<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\SchedulerService;
use Illuminate\Http\Request;
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
     * Confirm and save schedules from preview
     */
    public function confirm(Request $request)
    {
        try {
            Log::info('=== CONFIRM SCHEDULE START ===');
            Log::info('Request Method: ' . $request->method());
            Log::info('Content Type: ' . $request->header('Content-Type'));
            Log::info('Raw Input:', ['input' => $request->all()]);
            
            $scheduleType = $request->input('schedule_type', 'regular');
            $schedules = $request->input('schedules', []);
            $examinations = $request->input('examinations', []);

            Log::info('Parsed Data:', [
                'schedule_type' => $scheduleType,
                'schedule_count' => count($schedules),
                'exam_count' => count($examinations)
            ]);

            // Validate data
            if ($scheduleType === 'examination') {
                if (empty($examinations)) {
                    Log::warning('No examination schedules provided');
                    return response()->json([
                        'success' => false,
                        'message' => 'No examination schedules provided'
                    ], 400);
                }
                $dataToSave = [];
                $examsToSave = $examinations;
            } else {
                if (empty($schedules)) {
                    Log::warning('No regular schedules provided');
                    return response()->json([
                        'success' => false,
                        'message' => 'No regular schedules provided'
                    ], 400);
                }
                $dataToSave = $schedules;
                $examsToSave = [];
            }

            Log::info('Calling schedulerService->saveSchedule...');
            $result = $this->schedulerService->saveSchedule($dataToSave, $examsToSave);

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
            // Return the blade view with AJAX functionality
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
            Log::info('Request headers:', [
                'Accept' => $request->header('Accept'),
                'Content-Type' => $request->header('Content-Type'),
                'X-Requested-With' => $request->header('X-Requested-With'),
            ]);

            // Fetch all schedules
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->orderBy('start_time')
                ->get();

            Log::info('Schedules fetched', ['count' => $schedules->count()]);

            // Assign colors to subjects for consistency
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

            Log::info('Returning JSON response', [
                'schedule_count' => count($schedules),
                'subject_colors_count' => count($subjectColors)
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('=== ERROR IN SCHEDULE DATA API ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
     * Download schedules as PDF - UPDATED
     */
    public function downloadPDF(Request $request)
    {
        try {
            // Fetch all schedules
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->orderBy('start_time')
                ->get();

            // Prepare data (same as printSchedule)
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $timeSlots = [
                '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', 
                '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'
            ];
            
            $schedulesByDayAndTime = [];
            foreach($days as $day) {
                $schedulesByDayAndTime[$day] = [];
                foreach($timeSlots as $time) {
                    $schedulesByDayAndTime[$day][$time] = [];
                }
            }
            
            $occupiedCells = [];
            
            foreach($schedules as $schedule) {
                $day = $schedule->day;
                $startTime = substr($schedule->start_time, 0, 5);
                $endTime = substr($schedule->end_time, 0, 5);
                
                if(!in_array($day, $days)) {
                    continue;
                }
                
                $startHour = (int)substr($startTime, 0, 2);
                $endHour = (int)substr($endTime, 0, 2);
                $startMin = (int)substr($startTime, 3, 2);
                $endMin = (int)substr($endTime, 3, 2);
                
                $duration = $endHour - $startHour;
                if($endMin > 0) {
                    $duration++;
                }
                
                $closestSlot = $startTime;
                $minDiff = 9999;
                foreach($timeSlots as $slot) {
                    $slotHour = (int)substr($slot, 0, 2);
                    $diff = abs($slotHour - $startHour);
                    if($diff < $minDiff) {
                        $minDiff = $diff;
                        $closestSlot = $slot;
                    }
                }
                
                if(isset($schedulesByDayAndTime[$day][$closestSlot])) {
                    $schedule->calculated_rowspan = max(1, $duration);
                    $schedulesByDayAndTime[$day][$closestSlot][] = $schedule;
                    
                    for($i = 1; $i < $duration; $i++) {
                        $nextTimeIndex = array_search($closestSlot, $timeSlots) + $i;
                        if($nextTimeIndex < count($timeSlots)) {
                            $nextTime = $timeSlots[$nextTimeIndex];
                            if(!isset($occupiedCells[$day])) {
                                $occupiedCells[$day] = [];
                            }
                            $occupiedCells[$day][$nextTime] = true;
                        }
                    }
                }
            }
            
            $colors = [
                'pink', 'blue', 'green', 'yellow', 'purple', 'red', 
                'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia'
            ];
            
            $subjectColors = [];
            $colorIndex = 0;
            
            foreach($schedules as $schedule) {
                if(!isset($subjectColors[$schedule->subject_id])) {
                    $subjectColors[$schedule->subject_id] = $colors[$colorIndex % count($colors)];
                    $colorIndex++;
                }
            }

            // Return print view (user will use browser's "Save as PDF")
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

            // Excel generation logic here
            // return Excel::download(new SchedulesExport($schedules), 'schedules-' . date('Y-m-d') . '.xlsx');

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

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error clearing schedules: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Get day number for FullCalendar (0 = Sunday, 1 = Monday, etc.)
     */
    private function getDayNumber($day)
    {
        $days = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6
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

        // Calculate days until next occurrence
        $daysUntil = ($dayNumber - $currentDay + 7) % 7;
        if ($daysUntil === 0 && $now->format('H:i:s') > $time) {
            $daysUntil = 7;
        }

        return $now->addDays($daysUntil)->format('Y-m-d') . 'T' . $time;
    }
}
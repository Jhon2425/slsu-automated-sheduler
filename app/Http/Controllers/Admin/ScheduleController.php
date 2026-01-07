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
     * Download schedules as PDF
     */
    public function downloadPDF(Request $request)
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->orderBy('start_time')
                ->get();

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
                
                if(!in_array($day, $days)) continue;
                
                $startHour = (int)substr($startTime, 0, 2);
                $endHour = (int)substr($endTime, 0, 2);
                $duration = $endHour - $startHour;
                if((int)substr($endTime, 3, 2) > 0) $duration++;
                
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
                            if(!isset($occupiedCells[$day])) $occupiedCells[$day] = [];
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
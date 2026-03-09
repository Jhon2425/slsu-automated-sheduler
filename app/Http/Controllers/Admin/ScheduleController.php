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

    // Day number to name mapping — single source of truth for this controller
    private $dayNames = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
    ];

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    /**
     * Display all schedules with proper day handling.
     * FIXED: Adds day_name to every schedule so the Blade timetable can render it correctly.
     */
    public function index()
    {
        $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
            ->orderByRaw("FIELD(day, 1, 2, 3, 4, 5, 6, 7)")
            ->orderBy('start_time')
            ->paginate(1000);

        // FIXED: Inject day_name onto every schedule model so the Blade view never
        // has to guess and never silently drops a row due to a null/empty day_name.
        $dayNames = $this->dayNames;
        $schedules->getCollection()->transform(function ($schedule) use ($dayNames) {
            $schedule->day_name = $dayNames[$schedule->day] ?? 'Monday';
            return $schedule;
        });

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show schedule generation page with faculty data.
     * ENHANCED: Now includes faculty unavailability information.
     */
    public function create()
    {
        $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');

        $faculties = User::where('role_id', $facultyRoleId)
            ->with(['faculty.facultySubjects.subject'])
            ->orderBy('name')
            ->get();

        // Get faculty unavailability summary
        $facultyUnavailability = DB::table('faculty_unavailabilities')
            ->join('users', 'faculty_unavailabilities.faculty_code', '=', 'users.faculty_code')
            ->select(
                'users.name as faculty_name',
                'users.faculty_code',
                DB::raw('COUNT(*) as unavailable_slots')
            )
            ->groupBy('users.faculty_code', 'users.name')
            ->get();

        $stats = [
            'total_faculty'              => $faculties->count(),
            'total_assignments'          => FacultySubject::count(),
            'unique_subjects'            => Subject::whereHas('facultySubjects')->count(),
            'programs_count'             => Program::count(),
            'faculties_with_restrictions'=> $facultyUnavailability->count(),
            'total_unavailable_slots'    => $facultyUnavailability->sum('unavailable_slots'),
        ];

        return view('admin.schedules.generate', compact('faculties', 'stats', 'facultyUnavailability'));
    }

    /**
     * Show a single schedule (for modal view).
     */
    public function show($id)
    {
        try {
            $schedule = Schedule::with(['subject', 'faculty', 'classroom'])->findOrFail($id);

            // Attach day_name for consistency
            $schedule->day_name = $this->dayNames[$schedule->day] ?? 'Monday';

            if (request()->ajax()) {
                return response()->json([
                    'success'  => true,
                    'schedule' => $schedule,
                ]);
            }

            return view('admin.schedules.show', compact('schedule'));

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found',
                ], 404);
            }

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Schedule not found');
        }
    }

    /**
     * Generate preview using the SchedulerService.
     * ENHANCED: Better error handling and validation.
     */
    public function generatePreview(Request $request)
    {
        try {
            Log::info('=== GENERATE PREVIEW START ===', [
                'schedule_type' => $request->input('schedule_type'),
                'request_data'  => $request->all(),
            ]);

            $assignmentCount = FacultySubject::count();
            if ($assignmentCount === 0) {
                return response()->json([
                    'success'      => false,
                    'message'      => 'No faculty-subject assignments found. Please assign subjects to faculty first.',
                    'schedules'    => [],
                    'examinations' => [],
                    'conflicts'    => [],
                ]);
            }

            $unavailabilityCount = DB::table('faculty_unavailabilities')->count();
            if ($unavailabilityCount > 0) {
                Log::info('Faculty unavailability data found', [
                    'total_restrictions' => $unavailabilityCount,
                ]);
            }

            $result = $this->schedulerService->generateSchedulePreview();

            Log::info('=== SCHEDULE PREVIEW GENERATED ===', [
                'success'        => $result['success'],
                'schedule_count' => count($result['schedules']),
                'exam_count'     => count($result['examinations']),
                'conflict_count' => count($result['conflicts']),
            ]);

            if ($result['success']) {
                session([
                    'schedule_preview'    => $result['schedules'],
                    'examination_preview' => $result['examinations'],
                    'schedule_conflicts'  => $result['conflicts'],
                ]);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('=== ERROR IN GENERATE PREVIEW ===', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'      => false,
                'message'      => 'Error generating preview: ' . $e->getMessage(),
                'schedules'    => [],
                'examinations' => [],
                'conflicts'    => [],
            ], 500);
        }
    }

    /**
     * Generate examination preview separately.
     */
    public function generateExaminationPreview(Request $request)
    {
        try {
            Log::info('=== GENERATE EXAMINATION PREVIEW START ===');

            $assignmentCount = FacultySubject::count();
            if ($assignmentCount === 0) {
                return response()->json([
                    'success'      => false,
                    'message'      => 'No faculty-subject assignments found. Please assign subjects to faculty first.',
                    'examinations' => [],
                    'conflicts'    => [],
                ]);
            }

            $unavailabilityCount = DB::table('faculty_unavailabilities')->count();
            if ($unavailabilityCount > 0) {
                Log::info('Faculty unavailability data found for exam scheduling', [
                    'total_restrictions' => $unavailabilityCount,
                ]);
            }

            $result = $this->schedulerService->generateExaminationPreview();

            Log::info('=== EXAMINATION PREVIEW GENERATED ===', [
                'success'       => $result['success'],
                'exam_count'    => count($result['examinations']),
                'conflict_count'=> count($result['conflicts']),
            ]);

            if ($result['success']) {
                session([
                    'examination_preview'  => $result['examinations'],
                    'examination_conflicts' => $result['conflicts'],
                ]);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('=== ERROR IN GENERATE EXAMINATION PREVIEW ===', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'      => false,
                'message'      => 'Error generating examination preview: ' . $e->getMessage(),
                'examinations' => [],
                'conflicts'    => [],
            ], 500);
        }
    }

    /**
     * Review generated schedule before saving.
     */
    public function review()
    {
        $schedules    = session('schedule_preview', []);
        $examinations = session('examination_preview', []);
        $conflicts    = session('schedule_conflicts', []);

        if (empty($schedules) && empty($examinations)) {
            return redirect()->route('admin.schedules.create')
                ->with('error', 'No schedule preview available. Please generate a schedule first.');
        }

        $schedulesByDay      = collect($schedules)->groupBy('day_name');
        $examinationsByDate  = collect($examinations)->groupBy('exam_date');

        $unavailabilityConflicts = collect($conflicts)->filter(function ($conflict) {
            return isset($conflict['unavailability_count']) && $conflict['unavailability_count'] > 0;
        });

        $otherConflicts = collect($conflicts)->filter(function ($conflict) {
            return !isset($conflict['unavailability_count']) || $conflict['unavailability_count'] == 0;
        });

        return view('admin.schedules.review', compact(
            'schedules',
            'examinations',
            'conflicts',
            'schedulesByDay',
            'examinationsByDate',
            'unavailabilityConflicts',
            'otherConflicts'
        ));
    }

    /**
     * Confirm and save schedules from preview/review.
     */
    public function confirm(Request $request)
    {
        try {
            Log::info('=== CONFIRM SCHEDULE START ===');
            Log::info('Request Method: ' . $request->method());

            $sessionSchedules = session('schedule_preview', []);
            $sessionExams     = session('examination_preview', []);

            $schedules     = $request->input('schedules', $sessionSchedules);
            $examinations  = $request->input('examinations', $sessionExams);
            $scheduleType  = $request->input('schedule_type', 'regular');

            Log::info('Parsed Data:', [
                'schedule_type'    => $scheduleType,
                'schedule_count'   => count($schedules),
                'exam_count'       => count($examinations),
                'has_session_data' => !empty($sessionSchedules) || !empty($sessionExams),
            ]);

            if (empty($schedules) && empty($examinations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules or examinations provided. Please generate a schedule first.',
                ], 400);
            }

            $validationErrors = [];
            foreach ($schedules as $index => $schedule) {
                if (empty($schedule['faculty_code'])) {
                    $validationErrors[] = "Schedule {$index}: Missing faculty_code";
                }
                if (empty($schedule['subject_id'])) {
                    $validationErrors[] = "Schedule {$index}: Missing subject_id";
                }
                if (empty($schedule['classroom_id'])) {
                    $validationErrors[] = "Schedule {$index}: Missing classroom_id";
                }
            }

            if (!empty($validationErrors)) {
                Log::warning('Schedule validation failed', ['errors' => $validationErrors]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors: ' . implode(', ', array_slice($validationErrors, 0, 3)),
                    'errors'  => $validationErrors,
                ], 400);
            }

            Log::info('Calling schedulerService->saveSchedule...');
            $result = $this->schedulerService->saveSchedule($schedules, $examinations);

            if ($result['success']) {
                session()->forget([
                    'schedule_preview',
                    'examination_preview',
                    'schedule_conflicts',
                    'examination_conflicts',
                ]);
            }

            Log::info('=== SCHEDULE SAVE RESULT ===', [
                'success'         => $result['success'],
                'saved_schedules' => $result['saved_schedules'] ?? 0,
                'saved_exams'     => $result['saved_exams'] ?? 0,
            ]);
            Log::info('=== CONFIRM SCHEDULE END ===');

            return response()->json($result);

        } catch (\Throwable $e) {
            Log::error('=== FATAL ERROR IN CONFIRM ===', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Critical error: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    /**
     * Confirm and save only examinations.
     */
    public function confirmExaminations(Request $request)
    {
        try {
            Log::info('=== CONFIRM EXAMINATIONS START ===');

            $sessionExams = session('examination_preview', []);
            $examinations = $request->input('examinations', $sessionExams);

            if (empty($examinations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No examinations provided',
                ], 400);
            }

            Log::info('Saving examinations', ['count' => count($examinations)]);

            $result = $this->schedulerService->saveExaminations($examinations);

            if ($result['success']) {
                session()->forget(['examination_preview', 'examination_conflicts']);
            }

            Log::info('=== CONFIRM EXAMINATIONS END ===', [
                'success'    => $result['success'],
                'saved_exams'=> $result['saved_exams'] ?? 0,
            ]);

            return response()->json($result);

        } catch (\Throwable $e) {
            Log::error('=== ERROR IN CONFIRM EXAMINATIONS ===', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving examinations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel preview and go back.
     */
    public function cancel()
    {
        session()->forget([
            'schedule_preview',
            'examination_preview',
            'schedule_conflicts',
            'examination_conflicts',
        ]);

        return redirect()->route('admin.schedules.create')
            ->with('info', 'Schedule preview cancelled.');
    }

    /**
     * View previous schedules history.
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
            Log::error('Error viewing previous schedules', ['message' => $e->getMessage()]);

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error loading previous schedules: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar data for FullCalendar integration.
     */
    public function getCalendarData(Request $request)
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->where('is_active', true)
                ->get();

            $events = $schedules->map(function ($schedule) {
                $dayName   = $this->dayNames[$schedule->day] ?? 'Monday';
                $dayNumber = $this->getDayNumber($dayName);

                return [
                    'id'          => $schedule->id,
                    'title'       => $schedule->subject->subject_name ?? $schedule->subject->name ?? 'N/A',
                    'start'       => $this->getNextOccurrence($dayName, $schedule->start_time),
                    'end'         => $this->getNextOccurrence($dayName, $schedule->end_time),
                    'daysOfWeek'  => [$dayNumber],
                    'startTime'   => $schedule->start_time,
                    'endTime'     => $schedule->end_time,
                    'extendedProps' => [
                        'faculty'    => $schedule->faculty->name ?? 'N/A',
                        'classroom'  => $schedule->classroom->room_name ?? $schedule->classroom->name ?? 'N/A',
                        'type'       => $schedule->class_type ?? 'regular',
                        'year_level' => $schedule->year_level,
                        'section'    => $schedule->section,
                    ],
                ];
            });

            return response()->json($events);

        } catch (\Exception $e) {
            Log::error('Error getting calendar data', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading calendar data',
            ], 500);
        }
    }

    /**
     * Print schedule view.
     */
    public function printSchedule()
    {
        try {
            return view('admin.schedules.print-ajax');

        } catch (\Exception $e) {
            Log::error('Error loading print view', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error loading print view: ' . $e->getMessage());
        }
    }

    /**
     * Get schedule data as JSON for AJAX requests.
     * FIXED: day_name always populated from day number.
     */
    public function getScheduleData(Request $request)
    {
        try {
            Log::info('=== SCHEDULE DATA API CALLED ===');

            $dayNames = $this->dayNames;

            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 1, 2, 3, 4, 5, 6, 7)")
                ->orderBy('start_time')
                ->get()
                ->map(function ($schedule) use ($dayNames) {
                    $schedule->day_name = $dayNames[$schedule->day] ?? 'Unknown';
                    return $schedule;
                });

            Log::info('Schedules fetched', ['count' => $schedules->count()]);

            $colors = [
                'pink', 'blue', 'green', 'yellow', 'purple', 'red',
                'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia',
            ];

            $subjectColors = [];
            $colorIndex    = 0;

            foreach ($schedules as $schedule) {
                if ($schedule->subject_id && !isset($subjectColors[$schedule->subject_id])) {
                    $subjectColors[$schedule->subject_id] = $colors[$colorIndex % count($colors)];
                    $colorIndex++;
                }
            }

            return response()->json([
                'success'       => true,
                'schedules'     => ['data' => $schedules],
                'subjectColors' => $subjectColors,
            ]);

        } catch (\Exception $e) {
            Log::error('=== ERROR IN SCHEDULE DATA API ===', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'       => false,
                'message'       => 'Error loading schedule data: ' . $e->getMessage(),
                'schedules'     => ['data' => []],
                'subjectColors' => [],
            ], 500);
        }
    }

    /**
     * Download schedules as PDF.
     * FIXED: day_name resolved from day integer before grid plotting.
     */
    public function downloadPDF(Request $request)
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 1, 2, 3, 4, 5, 6, 7)")
                ->orderBy('start_time')
                ->get();

            $dayNames = $this->dayNames;

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            // 30-minute interval slots 07:00 – 19:00
            $timeSlots = [];
            for ($hour = 7; $hour <= 19; $hour++) {
                $timeSlots[] = sprintf('%02d:00', $hour);
                if ($hour < 19) {
                    $timeSlots[] = sprintf('%02d:30', $hour);
                }
            }

            $schedulesByDayAndTime = [];
            foreach ($days as $day) {
                $schedulesByDayAndTime[$day] = [];
                foreach ($timeSlots as $time) {
                    $schedulesByDayAndTime[$day][$time] = [];
                }
            }

            $occupiedCells = [];

            $colors = [
                'pink', 'blue', 'green', 'yellow', 'purple', 'red',
                'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia',
            ];

            $subjectColors = [];
            $colorIndex    = 0;

            foreach ($schedules as $schedule) {
                if ($schedule->subject_id && !isset($subjectColors[$schedule->subject_id])) {
                    $subjectColors[$schedule->subject_id] = $colors[$colorIndex % count($colors)];
                    $colorIndex++;
                }
            }

            foreach ($schedules as $schedule) {
                // FIXED: Always resolve day name from stored integer
                $dayName = $dayNames[$schedule->day] ?? null;

                if (!$dayName || !in_array($dayName, $days)) {
                    Log::warning('downloadPDF: day not in display list', [
                        'day_number' => $schedule->day,
                        'day_name'   => $dayName,
                    ]);
                    continue;
                }

                $startTime = substr($schedule->start_time, 0, 5);
                $endTime   = substr($schedule->end_time,   0, 5);

                list($sh, $sm) = explode(':', $startTime);
                list($eh, $em) = explode(':', $endTime);

                $startMinutes    = (int)$sh * 60 + (int)$sm;
                $endMinutes      = (int)$eh * 60 + (int)$em;
                $durationMinutes = $endMinutes - $startMinutes;
                $rowspan         = max(1, (int)ceil($durationMinutes / 30));

                // Exact match first, then nearest-earlier slot
                $matchedSlot = null;
                foreach ($timeSlots as $slot) {
                    if ($slot === $startTime) {
                        $matchedSlot = $slot;
                        break;
                    }
                }

                if (!$matchedSlot) {
                    $minDiff = PHP_INT_MAX;
                    foreach ($timeSlots as $slot) {
                        list($slotH, $slotM) = explode(':', $slot);
                        $slotMinutes = (int)$slotH * 60 + (int)$slotM;
                        if ($slotMinutes <= $startMinutes) {
                            $diff = $startMinutes - $slotMinutes;
                            if ($diff < $minDiff) {
                                $minDiff     = $diff;
                                $matchedSlot = $slot;
                            }
                        }
                    }
                }

                if (!$matchedSlot && count($timeSlots) > 0) {
                    $matchedSlot = $timeSlots[0];
                }

                if ($matchedSlot && isset($schedulesByDayAndTime[$dayName][$matchedSlot])) {
                    $schedule->calculated_rowspan = $rowspan;
                    $schedule->assigned_color     = $subjectColors[$schedule->subject_id] ?? 'gray';

                    $schedulesByDayAndTime[$dayName][$matchedSlot][] = $schedule;

                    $slotIndex = array_search($matchedSlot, $timeSlots);
                    if ($slotIndex !== false) {
                        if (!isset($occupiedCells[$dayName])) {
                            $occupiedCells[$dayName] = [];
                        }
                        for ($i = 1; $i < $rowspan; $i++) {
                            $nextIndex = $slotIndex + $i;
                            if ($nextIndex < count($timeSlots)) {
                                $occupiedCells[$dayName][$timeSlots[$nextIndex]] = true;
                            }
                        }
                    }
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
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download schedules as Excel.
     */
    public function downloadExcel()
    {
        try {
            return redirect()->route('admin.schedules.index')
                ->with('info', 'Excel download functionality to be implemented. Please install maatwebsite/excel');

        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }

    /**
     * Clear all schedules.
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
            }

            return redirect()->route('admin.schedules.index')
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            Log::error('Error clearing schedules', ['message' => $e->getMessage()]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error clearing schedules: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error clearing schedules: ' . $e->getMessage());
        }
    }

    /**
     * Test faculty unavailability (debug/verification).
     */
    public function testFacultyUnavailability($facultyCode)
    {
        try {
            $result = $this->schedulerService->testFacultyUnavailability($facultyCode);

            if (request()->ajax()) {
                return response()->json($result);
            }

            return view('admin.schedules.test-unavailability', compact('result'));

        } catch (\Exception $e) {
            Log::error('Error testing faculty unavailability', [
                'faculty_code' => $facultyCode,
                'message'      => $e->getMessage(),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error testing unavailability: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Get FullCalendar day number (0 = Sunday … 6 = Saturday).
     */
    private function getDayNumber($day): int
    {
        $map = [
            'Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2,
            'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6,
        ];
        return $map[$day] ?? 1;
    }

    /**
     * Get next occurrence of a weekday with a given time (for FullCalendar).
     */
    private function getNextOccurrence($day, $time): string
    {
        $dayNumber   = $this->getDayNumber($day);
        $now         = now();
        $currentDay  = $now->dayOfWeek;
        $daysUntil   = ($dayNumber - $currentDay + 7) % 7;

        if ($daysUntil === 0 && $now->format('H:i:s') > $time) {
            $daysUntil = 7;
        }

        return $now->addDays($daysUntil)->format('Y-m-d') . 'T' . $time;
    }
}
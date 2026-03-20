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

    private $dayNames = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
    ];

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    // =========================================================================
    // OJT DISPLAY HELPER
    // =========================================================================

    /**
     * Round the stored decimal OJT hours to nearest integer for display.
     * < .5 rounds down, >= .5 rounds up — standard PHP round().
     * Minimum 1 so a subject always shows at least one hour.
     */
    private function roundOjtForDisplay(?float $decimal): ?int
    {
        if ($decimal === null || $decimal <= 0) {
            return null;
        }
        return max(1, (int) round($decimal));
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
            ->orderByRaw("FIELD(day, 1, 2, 3, 4, 5, 6, 7)")
            ->orderBy('start_time')
            ->paginate(1000);

        $dayNames = $this->dayNames;

        $schedules->getCollection()->transform(function ($schedule) use ($dayNames) {
            $schedule->day_name          = $dayNames[$schedule->day] ?? 'Monday';
            $schedule->is_ojt            = strtolower($schedule->class_type ?? '') === 'ojt';
            $schedule->ojt_hours_display = $schedule->is_ojt
                ? $this->roundOjtForDisplay((float)($schedule->ojt_hours ?? 0))
                : null;
            return $schedule;
        });

        $sessionSummary = $schedules->getCollection()
            ->groupBy(fn($s) => $s->subject_id . '_' . ($s->class_type ?? 'Lecture'))
            ->map(function ($sessions) {
                $first      = $sessions->first();
                $totalHours = $sessions->sum('hours');
                $days       = $sessions->pluck('day_name')->unique()->sort()->values();
                $isOjt      = strtolower($first->class_type ?? '') === 'ojt';

                return [
                    'subject_name'      => optional($first->subject)->subject_name ?? 'N/A',
                    'course_code'       => optional($first->subject)->course_code ?? 'N/A',
                    'faculty_name'      => optional($first->faculty)->name ?? 'N/A',
                    'class_type'        => $first->class_type ?? 'Lecture',
                    'total_hours'       => $totalHours,
                    'session_count'     => $sessions->count(),
                    'days'              => $days->implode(', '),
                    'year_level'        => $first->year_level,
                    'section'           => $first->section,
                    'is_ojt'            => $isOjt,
                    'ojt_hours_display' => $isOjt
                        ? $this->roundOjtForDisplay((float)($first->ojt_hours ?? 0))
                        : null,
                ];
            })
            ->values();

        return view('admin.schedules.index', compact('schedules', 'sessionSummary'));
    }

    // =========================================================================
    // CREATE / GENERATE PAGE
    // =========================================================================

    public function create()
    {
        $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');

        $faculties = User::where('role_id', $facultyRoleId)
            ->with(['faculty.facultySubjects.subject'])
            ->orderBy('name')
            ->get();

        $facultyUnavailability = DB::table('faculty_unavailabilities')
            ->join('users', 'faculty_unavailabilities.faculty_code', '=', 'users.faculty_code')
            ->select(
                'users.name as faculty_name',
                'users.faculty_code',
                DB::raw('COUNT(*) as unavailable_slots')
            )
            ->groupBy('users.faculty_code', 'users.name')
            ->get();

        $estimatedRegularSessions = FacultySubject::whereNotNull('lecture_units')
            ->orWhereNotNull('laboratory_units')
            ->selectRaw(
                'SUM(CEIL(COALESCE(lecture_units, 0) / 2)) + SUM(CEIL((COALESCE(laboratory_units, 0) * 3) / 2)) as estimated'
            )->value('estimated') ?? 0;

        $estimatedOjtSessions = FacultySubject::whereNotNull('ojt_hours')
            ->where('ojt_hours', '>', 0)
            ->count();

        $stats = [
            'total_faculty'               => $faculties->count(),
            'total_assignments'           => FacultySubject::count(),
            'unique_subjects'             => Subject::whereHas('facultySubjects')->count(),
            'programs_count'              => Program::count(),
            'faculties_with_restrictions' => $facultyUnavailability->count(),
            'total_unavailable_slots'     => $facultyUnavailability->sum('unavailable_slots'),
            'estimated_sessions'          => (int) $estimatedRegularSessions + $estimatedOjtSessions,
            'ojt_assignments'             => $estimatedOjtSessions,
        ];

        return view('admin.schedules.generate', compact('faculties', 'stats', 'facultyUnavailability'));
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show($id)
    {
        try {
            $schedule = Schedule::with(['subject', 'faculty', 'classroom'])->findOrFail($id);
            $schedule->day_name          = $this->dayNames[$schedule->day] ?? 'Monday';
            $schedule->is_ojt            = strtolower($schedule->class_type ?? '') === 'ojt';
            $schedule->ojt_hours_display = $schedule->is_ojt
                ? $this->roundOjtForDisplay((float)($schedule->ojt_hours ?? 0))
                : null;

            $siblings = Schedule::with(['classroom'])
                ->where('subject_id', $schedule->subject_id)
                ->where('class_type', $schedule->class_type)
                ->where('id', '!=', $schedule->id)
                ->orderByRaw("FIELD(day, 1, 2, 3, 4, 5, 6, 7)")
                ->get()
                ->map(function ($s) {
                    $s->day_name          = $this->dayNames[$s->day] ?? 'Monday';
                    $s->is_ojt            = strtolower($s->class_type ?? '') === 'ojt';
                    $s->ojt_hours_display = $s->is_ojt
                        ? $this->roundOjtForDisplay((float)($s->ojt_hours ?? 0))
                        : null;
                    return $s;
                });

            if (request()->ajax()) {
                return response()->json([
                    'success'  => true,
                    'schedule' => $schedule,
                    'siblings' => $siblings,
                    'is_ojt'   => $schedule->is_ojt,
                ]);
            }

            return view('admin.schedules.show', compact('schedule', 'siblings'));

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Schedule not found'], 404);
            }
            return redirect()->route('admin.schedules.index')->with('error', 'Schedule not found');
        }
    }

    // =========================================================================
    // GENERATE PREVIEW
    // =========================================================================

    public function generatePreview(Request $request)
    {
        try {
            Log::info('=== GENERATE PREVIEW START ===', [
                'schedule_type' => $request->input('schedule_type'),
                'request_data'  => $request->all(),
            ]);

            // Include OJT assignments in the count check
            $assignmentCount = FacultySubject::where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('lecture_units')
                       ->orWhereNotNull('laboratory_units');
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('ojt_hours')->where('ojt_hours', '>', 0);
                });
            })->count();

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

            if ($result['success']) {
                // Inject ojt_hours_rounded into every OJT schedule row
                // so the frontend can display the rounded value directly
                $result['schedules'] = array_map(function ($s) {
                    $isOjt = strtolower($s['class_type'] ?? '') === 'ojt';
                    if ($isOjt && isset($s['ojt_hours']) && $s['ojt_hours'] > 0) {
                        $s['ojt_hours_rounded'] = $this->roundOjtForDisplay((float) $s['ojt_hours']);
                    } else {
                        $s['ojt_hours_rounded'] = null;
                    }
                    return $s;
                }, $result['schedules']);

                $result['session_summary'] = $this->buildSessionSummary($result['schedules']);

                $result['ojt_summary'] = array_values(array_filter(
                    $result['session_summary'],
                    fn($s) => strtolower($s['class_type']) === 'ojt'
                ));
            }

            Log::info('=== SCHEDULE PREVIEW GENERATED ===', [
                'success'        => $result['success'],
                'schedule_count' => count($result['schedules']),
                'conflict_count' => count($result['conflicts']),
                'ojt_count'      => count($result['ojt_summary'] ?? []),
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

    public function generateExaminationPreview(Request $request)
    {
        try {
            Log::info('=== GENERATE EXAMINATION PREVIEW START ===');

            $assignmentCount = FacultySubject::where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('lecture_units')
                       ->orWhereNotNull('laboratory_units');
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('ojt_hours')->where('ojt_hours', '>', 0);
                });
            })->count();

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
                'success'        => $result['success'],
                'exam_count'     => count($result['examinations']),
                'conflict_count' => count($result['conflicts']),
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

    // =========================================================================
    // REVIEW
    // =========================================================================

    public function review()
    {
        $schedules    = session('schedule_preview', []);
        $examinations = session('examination_preview', []);
        $conflicts    = session('schedule_conflicts', []);

        if (empty($schedules) && empty($examinations)) {
            return redirect()->route('admin.schedules.create')
                ->with('error', 'No schedule preview available. Please generate a schedule first.');
        }

        $schedulesByDay     = collect($schedules)->groupBy('day_name');
        $examinationsByDate = collect($examinations)->groupBy('exam_date');

        $sessionSummary = $this->buildSessionSummary($schedules);

        $lectureSessionCount = collect($schedules)->where('class_type', 'Lecture')->count();
        $labSessionCount     = collect($schedules)->where('class_type', 'Laboratory')->count();
        $ojtSessionCount     = collect($schedules)->filter(
            fn($s) => strtolower($s['class_type'] ?? '') === 'ojt'
        )->count();
        $uniqueSubjects = collect($schedules)->pluck('subject_id')->unique()->count();

        $ojtSummary = array_values(array_filter(
            $sessionSummary,
            fn($s) => strtolower($s['class_type']) === 'ojt'
        ));

        $unavailabilityConflicts = collect($conflicts)->filter(fn($c) =>
            isset($c['unavailability_count']) && $c['unavailability_count'] > 0
        );

        $otherConflicts = collect($conflicts)->filter(fn($c) =>
            !isset($c['unavailability_count']) || $c['unavailability_count'] == 0
        );

        return view('admin.schedules.review', compact(
            'schedules',
            'examinations',
            'conflicts',
            'schedulesByDay',
            'examinationsByDate',
            'sessionSummary',
            'lectureSessionCount',
            'labSessionCount',
            'ojtSessionCount',
            'ojtSummary',
            'uniqueSubjects',
            'unavailabilityConflicts',
            'otherConflicts'
        ));
    }

    // =========================================================================
    // CONFIRM / SAVE
    // =========================================================================

    public function confirm(Request $request)
    {
        try {
            Log::info('=== CONFIRM SCHEDULE START ===');

            $sessionSchedules = session('schedule_preview', []);
            $sessionExams     = session('examination_preview', []);

            $schedules    = $request->input('schedules', $sessionSchedules);
            $examinations = $request->input('examinations', $sessionExams);
            $scheduleType = $request->input('schedule_type', 'regular');

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

            $result = $this->schedulerService->saveSchedule($schedules, $examinations);

            if ($result['success']) {
                session()->forget([
                    'schedule_preview',
                    'examination_preview',
                    'schedule_conflicts',
                    'examination_conflicts',
                ]);

                $sessionCount = count($schedules);
                $subjectCount = count(array_unique(array_column($schedules, 'subject_id')));
                $ojtCount     = count(array_filter(
                    $schedules,
                    fn($s) => strtolower($s['class_type'] ?? '') === 'ojt'
                ));

                $result['message'] .= " ({$sessionCount} sessions across {$subjectCount} subjects";
                if ($ojtCount > 0) {
                    $result['message'] .= ", including {$ojtCount} OJT session(s)";
                }
                $result['message'] .= ')';
            }

            Log::info('=== SCHEDULE SAVE RESULT ===', [
                'success'         => $result['success'],
                'saved_schedules' => $result['saved_schedules'] ?? 0,
                'saved_exams'     => $result['saved_exams'] ?? 0,
            ]);

            return response()->json($result);

        } catch (\Throwable $e) {
            Log::error('=== FATAL ERROR IN CONFIRM ===', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'       => false,
                'message'       => 'Critical error: ' . $e->getMessage(),
                'error_details' => ['file' => $e->getFile(), 'line' => $e->getLine()],
            ], 500);
        }
    }

    public function confirmExaminations(Request $request)
    {
        try {
            Log::info('=== CONFIRM EXAMINATIONS START ===');

            $sessionExams = session('examination_preview', []);
            $examinations = $request->input('examinations', $sessionExams);

            if (empty($examinations)) {
                return response()->json(['success' => false, 'message' => 'No examinations provided'], 400);
            }

            $result = $this->schedulerService->saveExaminations($examinations);

            if ($result['success']) {
                session()->forget(['examination_preview', 'examination_conflicts']);
            }

            Log::info('=== CONFIRM EXAMINATIONS END ===', [
                'success'     => $result['success'],
                'saved_exams' => $result['saved_exams'] ?? 0,
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

    // =========================================================================
    // CANCEL
    // =========================================================================

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

    // =========================================================================
    // PREVIOUS SCHEDULES
    // =========================================================================

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

    // =========================================================================
    // CALENDAR DATA (FullCalendar)
    // =========================================================================

    public function getCalendarData(Request $request)
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->where('is_active', true)
                ->get();

            $events = $schedules->map(function ($schedule) {
                $dayName   = $this->dayNames[$schedule->day] ?? 'Monday';
                $dayNumber = $this->getDayNumber($dayName);
                $isOjt     = strtolower($schedule->class_type ?? '') === 'ojt';
                $ojtDisplay = $isOjt
                    ? $this->roundOjtForDisplay((float)($schedule->ojt_hours ?? 0))
                    : null;

                $sessionLabel = '';
                if ($isOjt && $ojtDisplay) {
                    $sessionLabel = " (OJT: {$ojtDisplay}h)";
                } elseif ($schedule->hours) {
                    $sessionLabel = " ({$schedule->hours}h)";
                }

                return [
                    'id'          => $schedule->id,
                    'title'       => ($schedule->subject->subject_name ?? 'N/A') . $sessionLabel,
                    'start'       => $this->getNextOccurrence($dayName, $schedule->start_time),
                    'end'         => $this->getNextOccurrence($dayName, $schedule->end_time),
                    'daysOfWeek'  => [$dayNumber],
                    'startTime'   => $schedule->start_time,
                    'endTime'     => $schedule->end_time,
                    'extendedProps' => [
                        'faculty'           => optional($schedule->faculty)->name ?? 'N/A',
                        'classroom'         => $schedule->classroom->room_name ?? $schedule->classroom->name ?? 'N/A',
                        'type'              => $schedule->class_type ?? 'Lecture',
                        'year_level'        => $schedule->year_level,
                        'section'           => $schedule->section,
                        'hours'             => $schedule->hours,
                        'class_type'        => $schedule->class_type,
                        'is_ojt'            => $isOjt,
                        'ojt_hours_raw'     => $isOjt ? $schedule->ojt_hours : null,
                        'ojt_hours_display' => $ojtDisplay,
                    ],
                ];
            });

            return response()->json($events);

        } catch (\Exception $e) {
            Log::error('Error getting calendar data', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Error loading calendar data'], 500);
        }
    }

    // =========================================================================
    // PRINT / PDF
    // =========================================================================

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

    // =========================================================================
    // SCHEDULE DATA API (AJAX)
    // =========================================================================

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
                    $schedule->day_name          = $dayNames[$schedule->day] ?? 'Unknown';
                    $schedule->is_ojt            = strtolower($schedule->class_type ?? '') === 'ojt';
                    $schedule->ojt_hours_display = $schedule->is_ojt
                        ? $this->roundOjtForDisplay((float)($schedule->ojt_hours ?? 0))
                        : null;
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

            $sessionSummary = $schedules
                ->groupBy(fn($s) => $s->subject_id . '_' . ($s->class_type ?? 'Lecture'))
                ->map(function ($sessions) {
                    $first = $sessions->first();
                    $isOjt = strtolower($first->class_type ?? '') === 'ojt';

                    return [
                        'subject_id'        => $first->subject_id,
                        'subject_name'      => optional($first->subject)->subject_name ?? 'N/A',
                        'class_type'        => $first->class_type ?? 'Lecture',
                        'session_count'     => $sessions->count(),
                        'total_hours'       => $sessions->sum('hours'),
                        'days'              => $sessions->pluck('day_name')->unique()->sort()->values(),
                        'is_ojt'            => $isOjt,
                        'ojt_hours_display' => $isOjt
                            ? $this->roundOjtForDisplay((float)($first->ojt_hours ?? 0))
                            : null,
                    ];
                })
                ->values();

            return response()->json([
                'success'        => true,
                'schedules'      => ['data' => $schedules],
                'subjectColors'  => $subjectColors,
                'sessionSummary' => $sessionSummary,
            ]);

        } catch (\Exception $e) {
            Log::error('=== ERROR IN SCHEDULE DATA API ===', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'        => false,
                'message'        => 'Error loading schedule data: ' . $e->getMessage(),
                'schedules'      => ['data' => []],
                'subjectColors'  => [],
                'sessionSummary' => [],
            ], 500);
        }
    }

    // =========================================================================
    // DOWNLOAD PDF
    // =========================================================================

    public function downloadPDF(Request $request)
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 1, 2, 3, 4, 5, 6, 7)")
                ->orderBy('start_time')
                ->get();

            $dayNames = $this->dayNames;
            $days     = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

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
                $dayName = $dayNames[$schedule->day] ?? null;

                if (!$dayName || !in_array($dayName, $days)) continue;

                $startTime = substr($schedule->start_time, 0, 5);
                $endTime   = substr($schedule->end_time, 0, 5);

                [$sh, $sm] = explode(':', $startTime);
                [$eh, $em] = explode(':', $endTime);

                $startMinutes    = (int)$sh * 60 + (int)$sm;
                $endMinutes      = (int)$eh * 60 + (int)$em;
                $durationMinutes = $endMinutes - $startMinutes;
                $rowspan         = max(1, (int)ceil($durationMinutes / 30));

                $matchedSlot = null;
                foreach ($timeSlots as $slot) {
                    if ($slot === $startTime) { $matchedSlot = $slot; break; }
                }

                if (!$matchedSlot) {
                    $minDiff = PHP_INT_MAX;
                    foreach ($timeSlots as $slot) {
                        [$slotH, $slotM] = explode(':', $slot);
                        $slotMinutes = (int)$slotH * 60 + (int)$slotM;
                        if ($slotMinutes <= $startMinutes) {
                            $diff = $startMinutes - $slotMinutes;
                            if ($diff < $minDiff) { $minDiff = $diff; $matchedSlot = $slot; }
                        }
                    }
                }

                if (!$matchedSlot && count($timeSlots) > 0) {
                    $matchedSlot = $timeSlots[0];
                }

                if ($matchedSlot && isset($schedulesByDayAndTime[$dayName][$matchedSlot])) {
                    $isOjt = strtolower($schedule->class_type ?? '') === 'ojt';

                    $schedule->calculated_rowspan = $rowspan;
                    $schedule->assigned_color     = $subjectColors[$schedule->subject_id] ?? 'gray';
                    $schedule->is_ojt             = $isOjt;
                    $schedule->ojt_hours_display  = $isOjt
                        ? $this->roundOjtForDisplay((float)($schedule->ojt_hours ?? 0))
                        : null;

                    $schedulesByDayAndTime[$dayName][$matchedSlot][] = $schedule;

                    $slotIndex = array_search($matchedSlot, $timeSlots);
                    if ($slotIndex !== false) {
                        if (!isset($occupiedCells[$dayName])) $occupiedCells[$dayName] = [];
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
                'schedules', 'days', 'timeSlots',
                'schedulesByDayAndTime', 'occupiedCells', 'subjectColors'
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

    // =========================================================================
    // DOWNLOAD EXCEL
    // =========================================================================

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

    // =========================================================================
    // CLEAR
    // =========================================================================

    public function clearAllSchedules()
    {
        try {
            $result = $this->schedulerService->clearAllSchedules();

            if (request()->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('admin.schedules.index')
                ->with($result['success'] ? 'success' : 'error', $result['message']);

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

    // =========================================================================
    // DIAGNOSTICS
    // =========================================================================

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
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }

            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error testing unavailability: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function buildSessionSummary(array $schedules): array
    {
        $grouped = [];

        foreach ($schedules as $s) {
            $key   = ($s['subject_id'] ?? '') . '_' . ($s['class_type'] ?? 'Lecture');
            $isOjt = strtolower($s['class_type'] ?? '') === 'ojt';

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'subject_id'        => $s['subject_id']     ?? null,
                    'subject_name'      => $s['course_subject']  ?? 'N/A',
                    'course_code'       => $s['course_code']    ?? 'N/A',
                    'faculty_name'      => $s['faculty_name']   ?? 'N/A',
                    'faculty_code'      => $s['faculty_code']   ?? 'N/A',
                    'class_type'        => $s['class_type']     ?? 'Lecture',
                    'year_level'        => $s['year_level']     ?? null,
                    'year_section'      => $s['year_section']   ?? null,
                    'semester'          => $s['semester']       ?? null,
                    'total_hours'       => 0,
                    'session_count'     => 0,
                    'sessions'          => [],
                    'is_ojt'            => $isOjt,
                    'ojt_hours_display' => $isOjt
                        ? $this->roundOjtForDisplay((float)($s['ojt_hours'] ?? 0))
                        : null,
                ];
            }

            $hours = $s['hours'] ?? 0;
            $grouped[$key]['total_hours']   += $hours;
            $grouped[$key]['session_count'] += 1;
            $grouped[$key]['sessions'][]     = [
                'day'               => $s['day_name']   ?? $s['day'] ?? 'N/A',
                'start_time'        => $s['start_time'] ?? '',
                'end_time'          => $s['end_time']   ?? '',
                'hours'             => $hours,
                'classroom'         => $s['classroom_name'] ?? 'N/A',
                'is_ojt'            => $isOjt,
                'ojt_hours_display' => $isOjt
                    ? $this->roundOjtForDisplay((float)($s['ojt_hours'] ?? 0))
                    : null,
            ];
        }

        $dayOrder = array_flip(array_values($this->dayNames));
        foreach ($grouped as &$entry) {
            usort($entry['sessions'], fn($a, $b) =>
                ($dayOrder[$a['day']] ?? 99) <=> ($dayOrder[$b['day']] ?? 99)
            );
        }
        unset($entry);

        return array_values($grouped);
    }

    private function getDayNumber($day): int
    {
        $map = [
            'Sunday'    => 0, 'Monday' => 1, 'Tuesday'  => 2,
            'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6,
        ];
        return $map[$day] ?? 1;
    }

    private function getNextOccurrence($day, $time): string
    {
        $dayNumber  = $this->getDayNumber($day);
        $now        = now();
        $currentDay = $now->dayOfWeek;
        $daysUntil  = ($dayNumber - $currentDay + 7) % 7;

        if ($daysUntil === 0 && $now->format('H:i:s') > $time) {
            $daysUntil = 7;
        }

        return $now->addDays($daysUntil)->format('Y-m-d') . 'T' . $time;
    }
}
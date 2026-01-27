<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Examination;
use App\Models\Classroom;
use App\Services\SchedulerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExaminationController extends Controller
{
    protected $schedulerService;

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    /**
     * Determine view path based on current route
     */
    private function getViewPath($viewName)
    {
        // Both routes use the same view directory: exam_schedules
        return 'admin.exam_schedules.' . $viewName;
    }

    /**
     * Determine route name based on current route
     */
    private function getRouteName($routeName)
    {
        if (request()->is('admin/exam-schedules*')) {
            return 'admin.exam-schedules.' . $routeName;
        }
        return 'admin.examinations.' . $routeName;
    }

    /**
     * Display all examinations
     */
    public function index(Request $request)
    {
        Log::info('=== EXAMINATION INDEX CALLED ===');
        
        $query = Examination::with(['subject', 'faculty', 'classroom']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('subject', function($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('classroom')) {
            $query->where('classroom_id', $request->classroom);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        }

        // Order by exam date
        $examinations = $query->orderBy('exam_date')
            ->orderBy('start_time')
            ->paginate(50);

        // Get classrooms for filter
        $classrooms = Classroom::orderBy('room_name')->get();

        Log::info('Examinations loaded', [
            'count' => $examinations->count(),
            'sample' => $examinations->take(2)->map(function($e) {
                return [
                    'id' => $e->id,
                    'exam_date' => $e->exam_date,
                    'subject' => $e->subject->subject_name ?? 'N/A',
                    'exam_type' => $e->exam_type
                ];
            })->toArray()
        ]);

        return view($this->getViewPath('index'), compact('examinations', 'classrooms'));
    }

    /**
     * Show the form for creating a new examination
     */
    public function create()
    {
        $classrooms = Classroom::orderBy('room_name')->get();
        return view($this->getViewPath('create'), compact('classrooms'));
    }

    /**
     * Store a newly created examination
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'classroom_id' => 'required|exists:classrooms,id',
                'exam_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'exam_type' => 'required|in:Midterm,Final',
            ]);

            $examination = Examination::create($validated);

            return redirect()->route($this->getRouteName('index'))
                ->with('success', 'Examination created successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating examination: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing an examination
     */
    public function edit($id)
    {
        $examination = Examination::findOrFail($id);
        $classrooms = Classroom::orderBy('room_name')->get();
        return view($this->getViewPath('edit'), compact('examination', 'classrooms'));
    }

    /**
     * Update the specified examination
     */
    public function update(Request $request, $id)
    {
        try {
            $examination = Examination::findOrFail($id);
            
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'classroom_id' => 'required|exists:classrooms,id',
                'exam_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'exam_type' => 'required|in:Midterm,Final',
            ]);

            $examination->update($validated);

            return redirect()->route($this->getRouteName('index'))
                ->with('success', 'Examination updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating examination: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified examination
     */
    public function destroy($id)
    {
        try {
            $examination = Examination::findOrFail($id);
            $examination->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Examination deleted successfully'
                ]);
            }

            return redirect()->route($this->getRouteName('index'))
                ->with('success', 'Examination deleted successfully');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting examination'
                ], 500);
            }

            return redirect()->route($this->getRouteName('index'))
                ->with('error', 'Error deleting examination: ' . $e->getMessage());
        }
    }

    /**
     * Generate examination schedule preview
     */
    public function generatePreview(Request $request)
    {
        try {
            Log::info('Generate Examination Preview Request');

            $result = $this->schedulerService->generateSchedulePreview();

            if ($result['success'] && !empty($result['examinations'])) {
                // Store in session for review
                session([
                    'examination_preview' => $result['examinations'],
                    'examination_conflicts' => $result['conflicts'] ?? []
                ]);

                return response()->json([
                    'success' => true,
                    'examinations' => $result['examinations'],
                    'conflicts' => $result['conflicts'] ?? [],
                    'message' => count($result['examinations']) . ' examinations generated successfully',
                    'stats' => [
                        'total_exams' => count($result['examinations']),
                        'total_conflicts' => count($result['conflicts'] ?? [])
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to generate examinations',
                    'examinations' => [],
                    'conflicts' => []
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in generatePreview (examinations)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating examination schedule: ' . $e->getMessage(),
                'examinations' => [],
                'conflicts' => []
            ], 500);
        }
    }

    /**
     * Confirm and save examinations
     */
    public function confirm(Request $request)
    {
        try {
            Log::info('=== CONFIRM EXAMINATIONS START ===');
            
            // Get from request or session
            $sessionExams = session('examination_preview', []);
            $examinations = $request->input('examinations', $sessionExams);

            Log::info('Parsed Examination Data:', [
                'exam_count' => count($examinations)
            ]);

            if (empty($examinations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No examinations provided'
                ], 400);
            }

            // Save examinations using the scheduler service
            $result = $this->schedulerService->saveSchedule([], $examinations);

            // Clear session data on success
            if ($result['success']) {
                session()->forget(['examination_preview', 'examination_conflicts']);
            }

            Log::info('Examination Save Result:', $result);
            Log::info('=== CONFIRM EXAMINATIONS END ===');

            return response()->json($result);

        } catch (\Throwable $e) {
            Log::error('=== FATAL ERROR IN CONFIRM EXAMINATIONS ===', [
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
     * View previous examinations (history)
     */
    public function viewPrevious()
    {
        try {
            $previousExaminations = Examination::with(['subject', 'faculty', 'classroom'])
                ->where('is_active', false)
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            return view($this->getViewPath('previous'), compact('previousExaminations'));

        } catch (\Exception $e) {
            Log::error('Error viewing previous examinations', [
                'message' => $e->getMessage()
            ]);

            return redirect()->route($this->getRouteName('index'))
                ->with('error', 'Error loading previous examinations: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar data for FullCalendar integration
     */
    public function getCalendarData(Request $request)
    {
        try {
            $examinations = Examination::with(['subject', 'faculty', 'classroom'])
                ->where('is_active', true)
                ->get();

            $events = $examinations->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'title' => ($exam->subject->course_code ?? 'Exam') . ' - ' . $exam->exam_type,
                    'start' => $exam->exam_date->format('Y-m-d') . 'T' . $exam->start_time,
                    'end' => $exam->exam_date->format('Y-m-d') . 'T' . $exam->end_time,
                    'backgroundColor' => $exam->exam_type === 'Final' ? '#dc2626' : '#f59e0b',
                    'extendedProps' => [
                        'subject' => $exam->subject->subject_name ?? 'N/A',
                        'faculty' => $exam->faculty->name ?? 'N/A',
                        'classroom' => $exam->classroom->room_name ?? 'N/A',
                        'exam_type' => $exam->exam_type,
                        'section' => $exam->year_section
                    ]
                ];
            });

            return response()->json($events);

        } catch (\Exception $e) {
            Log::error('Error getting examination calendar data', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading calendar data'
            ], 500);
        }
    }

    /**
     * Get examination data as JSON for AJAX requests
     */
    public function getExaminationData(Request $request)
    {
        try {
            Log::info('=== EXAMINATION DATA API CALLED ===');

            $examinations = Examination::with(['subject', 'faculty', 'classroom'])
                ->orderBy('exam_date')
                ->orderBy('start_time')
                ->get();

            Log::info('Examinations fetched', ['count' => $examinations->count()]);

            $response = [
                'success' => true,
                'examinations' => [
                    'data' => $examinations
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('=== ERROR IN EXAMINATION DATA API ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading examination data: ' . $e->getMessage(),
                'examinations' => ['data' => []]
            ], 500);
        }
    }

    /**
     * Print examination view
     */
    public function printExamination()
    {
        try {
            return view($this->getViewPath('print-ajax'));

        } catch (\Exception $e) {
            Log::error('Error loading examination print view', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route($this->getRouteName('index'))
                ->with('error', 'Error loading print view: ' . $e->getMessage());
        }
    }

    /**
     * Download examinations as PDF
     */
    public function downloadPDF()
    {
        try {
            $examinations = Examination::with(['subject', 'faculty', 'classroom'])
                ->orderBy('exam_date')
                ->orderBy('start_time')
                ->get();

            // Group by date
            $examsByDate = $examinations->groupBy(function($exam) {
                return $exam->exam_date->format('Y-m-d');
            });

            return view($this->getViewPath('pdf'), compact('examinations', 'examsByDate'));

        } catch (\Exception $e) {
            Log::error('Error generating examination PDF', [
                'message' => $e->getMessage()
            ]);
            
            return redirect()->route($this->getRouteName('index'))
                ->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download examinations as Excel
     */
    public function downloadExcel()
    {
        try {
            $examinations = Examination::with(['subject', 'faculty', 'classroom'])
                ->orderBy('exam_date')
                ->orderBy('start_time')
                ->get();

            return redirect()->route($this->getRouteName('index'))
                ->with('info', 'Excel download functionality to be implemented. Please install maatwebsite/excel');

        } catch (\Exception $e) {
            return redirect()->route($this->getRouteName('index'))
                ->with('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }

    /**
     * Clear all examinations
     */
    public function clearAllExaminations()
    {
        try {
            $result = $this->schedulerService->clearAllExaminations();

            if (request()->ajax()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return redirect()->route($this->getRouteName('index'))
                    ->with('success', $result['message']);
            } else {
                return redirect()->route($this->getRouteName('index'))
                    ->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Error clearing examinations', [
                'message' => $e->getMessage()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error clearing examinations: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route($this->getRouteName('index'))
                ->with('error', 'Error clearing examinations: ' . $e->getMessage());
        }
    }

    /**
     * Show single examination details
     */
    public function show($id)
    {
        try {
            $examination = Examination::with(['subject', 'faculty', 'classroom'])->findOrFail($id);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'examination' => $examination
                ]);
            }
            
            return view($this->getViewPath('show'), compact('examination'));
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examination not found'
                ], 404);
            }
            
            return redirect()->route($this->getRouteName('index'))
                ->with('error', 'Examination not found');
        }
    }
}
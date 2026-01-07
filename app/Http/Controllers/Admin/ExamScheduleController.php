<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Services\SchedulerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamScheduleController extends Controller
{
    protected $schedulerService;

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    /**
     * Display all exam schedules
     */
    public function index()
    {
        $examSchedules = ExamSchedule::with(['subject', 'faculty', 'classroom'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->paginate(50);

        return view('admin.exam_schedules.index', compact('examSchedules'));
    }

    /**
     * Show a single exam schedule (modal view)
     */
    public function show($id)
    {
        try {
            $exam = ExamSchedule::with(['subject', 'faculty', 'classroom'])->findOrFail($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'exam' => $exam
                ]);
            }

            return view('admin.exam_schedules.show', compact('exam'));

        } catch (\Exception $e) {
            Log::error('Error fetching exam schedule', ['message' => $e->getMessage()]);

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Exam schedule not found'], 404);
            }

            return redirect()->route('admin.exam_schedules.index')
                ->with('error', 'Exam schedule not found');
        }
    }

    /**
     * Generate exam schedule preview
     */
    public function generatePreview(Request $request)
    {
        try {
            $result = $this->schedulerService->generateExamSchedulePreview();

            Log::info('Exam schedule preview generated', [
                'exam_count' => count($result['examinations']),
                'conflict_count' => count($result['conflicts']),
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error generating exam schedule preview', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating exam schedule preview: ' . $e->getMessage(),
                'examinations' => [],
                'conflicts' => []
            ], 500);
        }
    }

    /**
     * Confirm and save exam schedules
     */
    public function confirm(Request $request)
    {
        try {
            $examinations = $request->input('examinations', []);

            if (empty($examinations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No examination schedules provided'
                ], 400);
            }

            $result = $this->schedulerService->saveExamSchedule([], $examinations);

            Log::info('Exam schedules saved', ['result' => $result]);

            return response()->json($result);

        } catch (\Throwable $e) {
            Log::error('Critical error confirming exam schedules', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Critical error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View previous exam schedules
     */
    public function viewPrevious()
    {
        $previousExams = ExamSchedule::with(['subject', 'faculty', 'classroom'])
            ->where('is_active', false)
            ->orderBy('exam_date', 'desc')
            ->paginate(50);

        return view('admin.exam_schedules.previous', compact('previousExams'));
    }

    /**
     * JSON data for calendar
     */
    public function getCalendarData()
    {
        $exams = ExamSchedule::with(['subject', 'faculty', 'classroom'])
            ->where('is_active', true)
            ->get();

        $events = $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'title' => $exam->subject->name ?? 'N/A',
                'start' => $exam->exam_date . 'T' . $exam->start_time,
                'end' => $exam->exam_date . 'T' . $exam->end_time,
                'extendedProps' => [
                    'faculty' => $exam->faculty->name ?? 'N/A',
                    'classroom' => $exam->classroom->name ?? 'N/A'
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Print exam schedules
     */
    public function printSchedule()
    {
        $exams = ExamSchedule::with(['subject', 'faculty', 'classroom'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        return view('admin.exam_schedules.print', compact('exams'));
    }

    /**
     * Download PDF
     */
    public function downloadPDF()
    {
        $exams = ExamSchedule::with(['subject', 'faculty', 'classroom'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        return view('admin.exam_schedules.pdf', compact('exams'));
    }

    /**
     * Download Excel (placeholder)
     */
    public function downloadExcel()
    {
        // Implement Excel export if needed
        return redirect()->route('admin.exam_schedules.index')
            ->with('info', 'Excel export functionality not yet implemented.');
    }

    /**
     * Clear all exam schedules
     */
    public function clearAllSchedules()
    {
        $result = $this->schedulerService->clearAllExamSchedules();

        if ($result['success']) {
            return redirect()->route('admin.exam_schedules.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.exam_schedules.index')
            ->with('error', $result['message']);
    }
}

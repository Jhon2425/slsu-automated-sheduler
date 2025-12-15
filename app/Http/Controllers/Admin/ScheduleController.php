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
            ->paginate(1000); // Get all schedules for timetable view

        return view('admin.schedules.index', compact('schedules'));
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
     * Confirm and save schedules from preview - WITH ENHANCED DEBUGGING
     */
    public function confirm(Request $request)
    {
        // Force JSON response even if there's an error
        try {
            Log::info('=== CONFIRM SCHEDULE START ===');
            Log::info('Request Method: ' . $request->method());
            Log::info('Request URL: ' . $request->url());
            Log::info('Content Type: ' . $request->header('Content-Type'));
            
            // Log raw input
            Log::info('Raw Input:', ['input' => $request->all()]);
            
            $scheduleType = $request->input('schedule_type', 'regular');
            $schedules = $request->input('schedules', []);
            $examinations = $request->input('examinations', []);

            Log::info('Parsed Data:', [
                'schedule_type' => $scheduleType,
                'has_schedules' => !empty($schedules),
                'has_examinations' => !empty($examinations),
                'schedule_count' => count($schedules),
                'exam_count' => count($examinations)
            ]);

            // Validate that we have data to save
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

            Log::info('Data prepared for saving', [
                'schedule_type' => $scheduleType,
                'regular_schedules_count' => count($dataToSave),
                'examination_schedules_count' => count($examsToSave)
            ]);

            // Log first item for debugging
            if (!empty($dataToSave)) {
                Log::info('First schedule sample:', ['schedule' => $dataToSave[0]]);
            }
            if (!empty($examsToSave)) {
                Log::info('First exam sample:', ['exam' => $examsToSave[0]]);
            }

            // Use the service to save
            Log::info('Calling schedulerService->saveSchedule...');
            $result = $this->schedulerService->saveSchedule($dataToSave, $examsToSave);

            Log::info('Schedule Save Result:', $result);
            Log::info('=== CONFIRM SCHEDULE END ===');

            return response()->json($result);

        } catch (\Throwable $e) {
            // Catch ALL errors including fatal errors
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
     * Download schedules as PDF
     */
    public function downloadPDF()
    {
        try {
            $schedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->orderBy('start_time')
                ->get();

            // Your PDF generation logic here
            // Example: return PDF::loadView('admin.schedules.pdf', compact('schedules'))->download('schedules.pdf');

            return redirect()->route('admin.schedules.index')
                ->with('info', 'PDF download functionality to be implemented');

        } catch (\Exception $e) {
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

            // Your Excel generation logic here
            // Example: return Excel::download(new SchedulesExport($schedules), 'schedules.xlsx');

            return redirect()->route('admin.schedules.index')
                ->with('info', 'Excel download functionality to be implemented');

        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }
}
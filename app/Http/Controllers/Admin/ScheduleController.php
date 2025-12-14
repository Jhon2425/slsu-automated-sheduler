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
            ]);
        }
    }

    /**
     * Confirm and save schedules from preview
     */
    public function confirm(Request $request)
    {
        try {
            Log::info('Confirm Schedule Request', [
                'schedule_type' => $request->input('schedule_type'),
                'schedule_count' => count($request->input('schedules', [])),
                'exam_count' => count($request->input('examinations', []))
            ]);

            $schedules = $request->input('schedules', []);
            $examinations = $request->input('examinations', []);
            $scheduleType = $request->input('schedule_type', 'regular');

            // Determine which data to save based on schedule type
            $dataToSave = $scheduleType === 'examination' ? [] : $schedules;
            $examsToSave = $scheduleType === 'examination' ? $examinations : $examinations;

            if (empty($dataToSave) && empty($examsToSave)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules to save'
                ]);
            }

            // Use the service to save
            $result = $this->schedulerService->saveSchedule($dataToSave, $examsToSave);

            Log::info('Schedule Save Result', $result);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error in confirm controller', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving schedules: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all schedules
     */
    public function clear()
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
    public function downloadPdf()
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
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SchedulerService;
use App\Models\Schedule;
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
     * Display all schedules
     */
    public function index()
    {
        $schedules = Schedule::with(['faculty', 'subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate(50);

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Generate schedule preview (returns JSON for modal)
     */
    public function generatePreview(Request $request)
    {
        try {
            $result = $this->schedulerService->generateSchedulePreview();
            
            if ($result['success']) {
                // Store schedules and examinations in session for confirmation
                session([
                    'pending_schedules' => $result['schedules'],
                    'pending_examinations' => $result['examinations'] ?? []
                ]);
                
                return response()->json([
                    'success' => true,
                    'schedules' => $result['schedules'],
                    'examinations' => $result['examinations'] ?? [],
                    'conflicts' => $result['conflicts'] ?? [],
                    'message' => $result['message']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);

        } catch (\Exception $e) {
            Log::error('Schedule generation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error generating schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm and save the generated schedule
     */
    public function confirmSchedule(Request $request)
    {
        try {
            Log::info('=== Starting schedule confirmation ===');
            
            // Get schedules from session first, then fallback to request
            $schedules = session('pending_schedules', []);
            $examinations = session('pending_examinations', []);
            
            // If not in session, try to get from request body
            if (empty($schedules)) {
                $schedules = $request->input('schedules', []);
                $examinations = $request->input('examinations', []);
            }
            
            Log::info('Schedules count: ' . count($schedules));
            Log::info('Examinations count: ' . count($examinations));

            if (empty($schedules)) {
                Log::warning('No schedules found in session or request');
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules to save. Please generate schedules first.'
                ], 422);
            }

            // Log first schedule for debugging
            if (count($schedules) > 0) {
                Log::info('First schedule data: ' . json_encode($schedules[0]));
            }

            // Save to database
            $result = $this->schedulerService->saveSchedule($schedules, $examinations);

            if ($result['success']) {
                Log::info('Schedules saved successfully');
                // Clear session data
                session()->forget(['pending_schedules', 'pending_examinations']);
            } else {
                Log::error('Failed to save schedules: ' . $result['message']);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Schedule confirmation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error saving schedule: ' . $e->getMessage()
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
                return redirect()->back()->with('success', $result['message']);
            }
            
            return redirect()->back()->with('error', $result['message']);

        } catch (\Exception $e) {
            Log::error('Schedule clear error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error clearing schedules: ' . $e->getMessage());
        }
    }

    /**
     * Download schedules as PDF
     */
    public function downloadPdf()
    {
        try {
            $schedules = Schedule::with(['faculty', 'subject', 'classroom'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            if ($schedules->isEmpty()) {
                return redirect()->back()->with('error', 'No schedules available to download');
            }

            $pdf = \PDF::loadView('admin.schedules.pdf', compact('schedules'));
            return $pdf->download('schedules_' . now()->format('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('PDF download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download schedules as Excel
     */
    public function downloadExcel()
    {
        try {
            $schedules = Schedule::with(['faculty', 'subject', 'classroom'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            if ($schedules->isEmpty()) {
                return redirect()->back()->with('error', 'No schedules available to download');
            }

            // Example:
            // return Excel::download(new SchedulesExport, 'schedules.xlsx');
            
            return redirect()->back()->with('info', 'Excel export feature coming soon');
            
        } catch (\Exception $e) {
            Log::error('Excel download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }
}
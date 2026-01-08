<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Examination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{
    /**
     * Display all previously generated schedules and examinations
     */
    public function index(Request $request)
    {
        try {
            Log::info('=== ARCHIVE INDEX CALLED ===');
            
            // Get class schedules with pagination
            $classSchedules = Schedule::with(['subject', 'faculty', 'classroom'])
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'page');
            
            // Get examination schedules with pagination
            $examSchedules = Examination::with(['subject', 'faculty', 'classroom'])
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'exam_page');
            
            // Get counts for tab badges
            $classScheduleCount = Schedule::count();
            $examScheduleCount = Examination::count();
            
            Log::info('Archive data loaded', [
                'class_count' => $classSchedules->count(),
                'exam_count' => $examSchedules->count(),
                'total_class' => $classScheduleCount,
                'total_exam' => $examScheduleCount
            ]);
            
            return view('admin.archives.view-saved-schedule', compact(
                'classSchedules',
                'examSchedules',
                'classScheduleCount',
                'examScheduleCount'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading archives', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error loading archives: ' . $e->getMessage());
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\SchedulerService;
use Barryvdh\DomPDF\Facade\Pdf;

class ScheduleController extends Controller
{
    private $schedulerService;

    public function __construct(SchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

    public function index()
    {
        $schedules = Schedule::with(['faculty', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return view('admin.schedules.index', compact('schedules'));
    }

    public function generate()
    {
        $result = $this->schedulerService->generateSchedule();

        if ($result['success']) {
            return redirect()->route('admin.schedules.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.schedules.index')
            ->with('error', $result['message']);
    }

    public function downloadPDF()
    {
        $schedules = Schedule::with(['faculty', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $pdf = Pdf::loadView('admin.schedules.pdf', compact('schedules'));
        return $pdf->download('complete-schedule.pdf');
    }
}

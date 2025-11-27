<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Faculty;
use Barryvdh\DomPDF\Facade\Pdf;

class FacultyDashboardController extends Controller
{
    public function index()
    {
        // Get faculty by logged-in user's name
        $faculty = Faculty::where('name', auth()->user()->name)->first();

        if (!$faculty) {
            return view('faculty.dashboard')->with('schedules', collect([]));
        }

        $schedules = Schedule::with(['classroom'])
            ->where('faculty_id', $faculty->id)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return view('faculty.dashboard', compact('schedules', 'faculty'));
    }

    public function downloadPDF()
    {
        $faculty = Faculty::where('name', auth()->user()->name)->first();

        if (!$faculty) {
            return redirect()->back()->with('error', 'No schedule found for your account.');
        }

        $schedules = Schedule::with(['classroom'])
            ->where('faculty_id', $faculty->id)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $pdf = Pdf::loadView('faculty.schedule-pdf', compact('schedules', 'faculty'));
        return $pdf->download('my-schedule.pdf');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Faculty; // if you use a Faculty model; otherwise replace with User
use App\Models\FacultyEnrollment;
use App\Models\Schedule;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Basic counts
        $totalFaculties   = Faculty::count();
        $totalPrograms    = Program::count();
        $totalSchedules   = Schedule::count();
        $totalClassrooms  = Classroom::count();
        $totalEnrollments = FacultyEnrollment::count();

        // Provide $programs so blade's $programs->count() works and to show program list if needed
        $programs = Program::withCount(['enrollments'])->latest()->get();

        // Recent items
        $recentPrograms  = Program::latest()->take(6)->get();
        $recentFaculties = Faculty::latest()->take(6)->get();

        // Pending enrollments with relations
        $pendingEnrollments = FacultyEnrollment::where('enrollment_status', 'pending')
            ->with(['faculty', 'program'])
            ->latest()
            ->get();

        // Some system stats derived from enrollments
        $avgClassSize = FacultyEnrollment::avg('no_of_students') ? round(FacultyEnrollment::avg('no_of_students'), 1) : 0;
        $totalHours   = (int) FacultyEnrollment::sum('no_of_hours');

        $labCount     = FacultyEnrollment::where('action_type', 'Laboratory')->count();
        $lectureCount = FacultyEnrollment::where('action_type', 'Lecture')->count();

        return view('admin.dashboard', compact(
            'totalFaculties',
            'totalPrograms',
            'totalSchedules',
            'totalClassrooms',
            'totalEnrollments',
            'programs',
            'recentPrograms',
            'recentFaculties',
            'pendingEnrollments',
            'avgClassSize',
            'totalHours',
            'labCount',
            'lectureCount'
        ));
    }
}

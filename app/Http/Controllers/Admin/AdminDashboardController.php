<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Subject; 
use App\Models\FacultyEnrollment;
use App\Models\Schedule;
use App\Models\Classroom;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Basic counts
        $totalSubjects    = Subject::count();
        $totalPrograms    = Program::count();
        $totalSchedules   = Schedule::count();
        $totalClassrooms  = Classroom::count();
        $totalEnrollments = FacultyEnrollment::count();

        // Provide $programs for dashboard display
        $programs = Program::withCount(['enrollments'])
            ->latest()
            ->get();

        // Recent programs only (faculties removed)
        $recentPrograms = Program::latest()->take(6)->get();

        // Pending enrollments with related program
        $pendingEnrollments = FacultyEnrollment::where('enrollment_status', 'pending')
            ->with(['program'])
            ->latest()
            ->get();

        // System statistics
        $avgClassSize = FacultyEnrollment::avg('no_of_students')
            ? round(FacultyEnrollment::avg('no_of_students'), 1)
            : 0;

        $totalHours = (int) FacultyEnrollment::sum('no_of_hours');

        $labCount     = FacultyEnrollment::where('action_type', 'Laboratory')->count();
        $lectureCount = FacultyEnrollment::where('action_type', 'Lecture')->count();

        return view('admin.dashboard', compact(
            'totalSubjects',
            'totalPrograms',
            'totalSchedules',
            'totalClassrooms',
            'totalEnrollments',
            'programs',
            'recentPrograms',
            'pendingEnrollments',
            'avgClassSize',
            'totalHours',
            'labCount',
            'lectureCount'
        ));
    }
}

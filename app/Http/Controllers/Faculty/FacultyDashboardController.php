<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\FacultyEnrollment;
use Illuminate\Support\Facades\Auth;

class FacultyDashboardController extends Controller
{
    public function index()
    {
        $facultyId = Auth::id();
        $user = Auth::user();

        // Get IDs of programs the faculty is already enrolled in
        $enrolledProgramIds = FacultyEnrollment::where('faculty_id', $facultyId)
            ->pluck('program_id')
            ->toArray();

        // Get available programs (not enrolled)
        $availablePrograms = Program::whereNotIn('id', $enrolledProgramIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get programs faculty is enrolled in with schedules
        $enrolledPrograms = FacultyEnrollment::where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get assigned subjects for this faculty member
        $assignedSubjects = $user->subjects()->with('program')->get();

        return view('faculty.dashboard', compact('availablePrograms', 'enrolledPrograms', 'assignedSubjects'));
    }

    /**
     * Enroll faculty to a program
     */
    public function enrollProgram($programId)
    {
        $facultyId = Auth::id();

        // Check if already enrolled
        $exists = FacultyEnrollment::where('faculty_id', $facultyId)
            ->where('program_id', $programId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'You are already enrolled in this program.');
        }

        FacultyEnrollment::create([
            'faculty_id' => $facultyId,
            'program_id' => $programId,
            'enrollment_status' => 'pending', // Admin needs to approve
        ]);

        return redirect()->back()->with('success', 'Successfully enrolled! Waiting for admin approval.');
    }

    /**
     * Unenroll from a program
     */
    public function unenrollProgram($enrollmentId)
    {
        $facultyId = Auth::id();

        $enrollment = FacultyEnrollment::where('id', $enrollmentId)
            ->where('faculty_id', $facultyId)
            ->firstOrFail();

        $enrollment->delete();

        return redirect()->back()->with('success', 'Successfully unenrolled from the program.');
    }

    /**
     * View schedule for a specific enrollment
     */
    public function viewSchedule($enrollmentId)
    {
        $facultyId = Auth::id();

        $enrollment = FacultyEnrollment::where('id', $enrollmentId)
            ->where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->firstOrFail();

        return view('faculty.schedule', compact('enrollment'));
    }

    /**
     * Download schedule for a specific enrollment
     */
    public function downloadSchedule($enrollmentId)
    {
        $facultyId = Auth::id();

        $enrollment = FacultyEnrollment::where('id', $enrollmentId)
            ->where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->firstOrFail();

        // Generate PDF logic here
        $pdf = \PDF::loadView('faculty.schedule-pdf', compact('enrollment'));
        
        return $pdf->download('schedule-' . $enrollment->program->code . '.pdf');
    }

    /**
     * Download all schedules (legacy)
     */
    public function downloadPDF()
    {
        $facultyId = Auth::id();
        
        $enrolledPrograms = FacultyEnrollment::where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->get();

        $pdf = \PDF::loadView('faculty.all-schedules-pdf', compact('enrolledPrograms'));
        
        return $pdf->download('my-schedules.pdf');
    }
}
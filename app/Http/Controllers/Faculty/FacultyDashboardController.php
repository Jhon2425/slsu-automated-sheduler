<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\FacultyEnrollment; // table to store faculty enrollments
use Illuminate\Support\Facades\Auth;

class FacultyDashboardController extends Controller
{
    public function index()
    {
        $facultyId = Auth::id();

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

        return view('faculty.dashboard', compact('availablePrograms', 'enrolledPrograms'));
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
}

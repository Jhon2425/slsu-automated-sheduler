<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\FacultyEnrollment;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    /**
     * Display a listing of faculties with enrollment stats.
     */
    public function index()
    {
        /*
         * Strategy:
         * - Aggregate faculty enrollment stats by faculty_id using the faculty_enrollments table
         * - Join to users to get faculty name / id and eager-load enrollments for action buttons
         *
         * This approach will work even if User model doesn't define enrollments relation.
         */

        // Aggregate counts from faculty_enrollments
        $stats = FacultyEnrollment::select(
                'faculty_id',
                DB::raw('COUNT(*) as enrollments_count'),
                DB::raw("SUM(enrollment_status = 'pending') as pending_count"),
                DB::raw("SUM(enrollment_status = 'active') as active_count")
            )
            ->groupBy('faculty_id')
            ->get()
            ->keyBy('faculty_id'); // keyed by faculty_id for quick lookup

        // Find all faculty users referenced in faculty_enrollments (or all users with role 'faculty' if you prefer)
        $facultyIds = $stats->keys()->toArray();

        // If there are no enrollments yet, return empty collection
        if (empty($facultyIds)) {
            $faculties = collect();
            return view('admin.programs.index', compact('faculties'));
        }

        // Load the faculty users and their enrollments
        $faculties = User::whereIn('id', $facultyIds)
            // eager load their enrollments so the blade can iterate $faculty->enrollments
            ->with(['enrollments' => function ($q) {
                $q->with('program'); // include program if needed in blade
            }])
            ->get()
            ->map(function ($user) use ($stats) {
                $stat = $stats->get($user->id);

                // attach aggregated counts to the user model for convenience in blade
                $user->enrollments_count = $stat ? (int)$stat->enrollments_count : 0;
                $user->pending_count = $stat ? (int)$stat->pending_count : 0;
                $user->active_count = $stat ? (int)$stat->active_count : 0;

                return $user;
            });

        return view('admin.programs.index', compact('faculties'));
    }

    /**
     * Show form for creating a new program
     * (kept for compatibility; if programs shouldn't be created via UI remove or restrict this)
     */
    public function create()
    {
        return view('admin.programs.create');
    }

    /**
     * Store a newly created program
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code',
            'description' => 'nullable|string',
            'semester' => 'required|string|in:1st Semester,2nd Semester,Summer',
            'academic_year' => 'required|string|regex:/^\d{4}-\d{4}$/',
        ], [
            'code.unique' => 'This program code is already in use.',
            'academic_year.regex' => 'Academic year must be in format: YYYY-YYYY (e.g., 2024-2025)',
        ]);

        $validated['status'] = 'active';

        Program::create($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program created successfully!');
    }

    /**
     * Display a specific program (kept if you still want to view program details)
     */
    public function show(Program $program)
    {
        $program->load(['enrollments.faculty', 'enrollments.schedules.classroom']);

        return view('admin.programs.show', compact('program'));
    }

    /**
     * Show form for editing program
     */
    public function edit(Program $program)
    {
        return view('admin.programs.edit', compact('program'));
    }

    /**
     * Update program
     */
    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code,' . $program->id,
            'description' => 'nullable|string',
            'semester' => 'required|string|in:1st Semester,2nd Semester,Summer',
            'academic_year' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'status' => 'required|in:active,inactive,completed',
        ]);

        $program->update($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program updated successfully!');
    }

    /**
     * Delete program
     */
    public function destroy(Program $program)
    {
        if ($program->enrollments()->count() > 0) {
            return redirect()->route('admin.programs.index')
                ->with('error', 'Cannot delete program with existing enrollments. Remove enrollments first.');
        }

        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program deleted successfully!');
    }

    /**
     * Show enrollment edit form
     */
    public function editEnrollment(FacultyEnrollment $enrollment)
    {
        $enrollment->load(['faculty', 'program']);
        $classrooms = Classroom::all();

        return view('admin.enrollments.edit', compact('enrollment', 'classrooms'));
    }

    /**
     * Update enrollment details
     */
    public function updateEnrollment(Request $request, FacultyEnrollment $enrollment)
    {
        $validated = $request->validate([
            'course_subject' => 'required|string|max:255',
            'year_section' => 'required|string|max:50',
            'no_of_students' => 'required|integer|min:1',
            'units' => 'required|numeric|min:0.5|max:10',
            'no_of_hours' => 'required|integer|min:1',
            'action_type' => 'required|string|in:Lecture,Laboratory,Both',
            'enrollment_status' => 'required|in:pending,active,completed',
        ]);

        $enrollment->update($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Enrollment updated! You can now assign schedules.');
    }

    /**
     * Show schedule assignment form
     */
    public function assignSchedule(FacultyEnrollment $enrollment)
    {
        $enrollment->load(['faculty', 'program', 'schedules.classroom']);
        $classrooms = Classroom::all();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('admin.enrollments.assign-schedule', compact('enrollment', 'classrooms', 'days'));
    }

    /**
     * Store schedules for enrollment
     */
    public function storeSchedule(Request $request, FacultyEnrollment $enrollment)
    {
        $validated = $request->validate([
            'schedules' => 'required|array|min:1',
            'schedules.*.classroom_id' => 'required|exists:classrooms,id',
            'schedules.*.day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.schedule_date' => 'required|date',
        ]);

        foreach ($validated['schedules'] as $data) {
            Schedule::create([
                'faculty_enrollment_id' => $enrollment->id,
                'classroom_id' => $data['classroom_id'],
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'schedule_date' => $data['schedule_date'],
            ]);
        }

        $enrollment->update(['enrollment_status' => 'active']);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Schedule assigned successfully!');
    }

    /**
     * Accept enrollment (set to active)
     */
    public function acceptEnrollment(FacultyEnrollment $enrollment)
    {
        $enrollment->update(['enrollment_status' => 'active']);
        return redirect()->route('admin.programs.index')->with('success', 'Enrollment accepted.');
    }

    /**
     * Decline enrollment (delete or set to declined)
     */
    public function declineEnrollment(FacultyEnrollment $enrollment)
    {
        // here we delete; change behavior if you want to mark as declined instead
        $enrollment->delete();
        return redirect()->route('admin.programs.index')->with('success', 'Enrollment declined and removed.');
    }
}

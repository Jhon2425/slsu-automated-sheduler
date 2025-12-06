<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\FacultyEnrollment;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display a listing of programs
     */
    public function index()
    {
        $programs = Program::withCount([
            'enrollments',
            'enrollments as pending_count' => function ($query) {
                $query->where('enrollment_status', 'pending');
            },
            'enrollments as active_count' => function ($query) {
                $query->where('enrollment_status', 'active');
            }
        ])
        ->with(['enrollments.faculty', 'enrollments.schedules'])
        ->latest()
        ->get();

        return view('admin.programs.index', compact('programs'));
    }

    /**
     * Show the form for creating a new program
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
            'academic_year.regex' => 'Academic year must be in format: YYYY-YYYY (e.g., 2024-2025)'
        ]);

        $validated['status'] = 'active';

        Program::create($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program created successfully!');
    }

    /**
     * Display the specified program
     */
    public function show(Program $program)
    {
        $program->load([
            'enrollments.faculty',
            'enrollments.schedules.classroom'
        ]);

        return view('admin.programs.show', compact('program'));
    }

    /**
     * Show the form for editing the program
     */
    public function edit(Program $program)
    {
        return view('admin.programs.edit', compact('program'));
    }

    /**
     * Update the specified program
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
     * Remove the specified program
     */
    public function destroy(Program $program)
    {
        // Check if program has enrollments
        if ($program->enrollments()->count() > 0) {
            return redirect()->route('admin.programs.index')
                ->with('error', 'Cannot delete program with existing enrollments. Remove enrollments first.');
        }

        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program deleted successfully!');
    }

    /**
     * Show enrollment details form
     */
    public function editEnrollment(FacultyEnrollment $enrollment)
    {
        $enrollment->load(['faculty', 'program']);
        $classrooms = \App\Models\Classroom::all();

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
            ->with('success', 'Enrollment details updated successfully! You can now assign schedules.');
    }

    /**
     * Show schedule assignment form
     */
    public function assignSchedule(FacultyEnrollment $enrollment)
    {
        $enrollment->load(['faculty', 'program', 'schedules.classroom']);
        $classrooms = \App\Models\Classroom::all();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('admin.enrollments.assign-schedule', compact('enrollment', 'classrooms', 'days'));
    }

    /**
     * Store schedule for enrollment
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

        foreach ($validated['schedules'] as $scheduleData) {
            \App\Models\Schedule::create([
                'faculty_enrollment_id' => $enrollment->id,
                'classroom_id' => $scheduleData['classroom_id'],
                'day' => $scheduleData['day'],
                'start_time' => $scheduleData['start_time'],
                'end_time' => $scheduleData['end_time'],
                'schedule_date' => $scheduleData['schedule_date'],
            ]);
        }

        // Update enrollment status to active
        $enrollment->update(['enrollment_status' => 'active']);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Schedule assigned successfully!');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\FacultyEnrollment;
use App\Models\Classroom;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
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
            'schedules'                 => 'required|array|min:1',
            'schedules.*.classroom_id'  => 'required|exists:classrooms,id',
            'schedules.*.day'           => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'schedules.*.start_time'    => 'required|date_format:H:i',
            'schedules.*.end_time'      => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.schedule_date' => 'required|date',
        ]);

        // Fetch the program directly from the programs table
        $program = Program::findOrFail($enrollment->program_id);

        foreach ($validated['schedules'] as $data) {
            Schedule::create([
                'faculty_enrollment_id' => $enrollment->id,
                'program_id'            => $program->id,
                'classroom_id'          => $data['classroom_id'],
                'day'                   => $data['day'],
                'start_time'            => $data['start_time'],
                'end_time'              => $data['end_time'],
                'schedule_date'         => $data['schedule_date'],
            ]);
        }

        $enrollment->update(['enrollment_status' => 'active']);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Schedule assigned successfully!');
    }

    /**
     * Show all schedules
     */
    public function index()
    {
        $schedules = Schedule::with(['facultyEnrollment.faculty', 'facultyEnrollment.program', 'classroom'])
            ->latest()
            ->get();

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show a specific schedule
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['facultyEnrollment.faculty', 'facultyEnrollment.program', 'classroom']);

        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Show form for editing a schedule
     */
    public function edit(Schedule $schedule)
    {
        $schedule->load(['facultyEnrollment.faculty', 'facultyEnrollment.program']);
        $classrooms = Classroom::all();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('admin.schedules.edit', compact('schedule', 'classrooms', 'days'));
    }

    /**
     * Update a schedule
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'classroom_id'  => 'required|exists:classrooms,id',
            'day'           => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'schedule_date' => 'required|date',
        ]);

        $schedule->update($validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated successfully!');
    }

    /**
     * Delete a schedule
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully!');
    }
}
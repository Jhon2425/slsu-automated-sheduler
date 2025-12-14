<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Program;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $programs = Program::all();
        
        $subjects = Subject::with('program')
            ->when($request->program_id, function ($query) use ($request) {
                return $query->where('program_id', $request->program_id);
            })
            ->orderBy('year_level')
            ->orderBy('semester')
            ->orderBy('course_code')
            ->paginate(15);

        return view('admin.subjects.index', compact('subjects', 'programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id'        => 'required|exists:programs,id',
            'course_code'       => 'required|string|max:20|unique:subjects,course_code',
            'subject_name'      => 'required|string|max:255',
            'units'             => 'required|numeric|min:0.5|max:6',
            'semester'          => 'required|in:1st Semester,2nd Semester,Summer',
            'year_level'        => 'required|integer|min:1|max:4',
            'enrolled_student'  => 'required|integer|min:0',  // ✅ ADDED
        ]);

        Subject::create($validated);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Subject created successfully and linked to the program!');
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'program_id'        => 'required|exists:programs,id',
            'course_code'       => 'required|string|max:20|unique:subjects,course_code,' . $subject->id,
            'subject_name'      => 'required|string|max:255',
            'units'             => 'required|numeric|min:0.5|max:6',
            'semester'          => 'required|in:1st Semester,2nd Semester,Summer',
            'year_level'        => 'required|integer|min:1|max:4',
            'enrolled_student'  => 'required|integer|min:0',  // ✅ ADDED
        ]);

        $subject->update($validated);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully!');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::latest()->paginate(10);
        return view('admin.faculties.index', compact('faculties'));
    }

    public function create()
    {
        return view('admin.faculties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_subject' => 'required|string|max:255',
            'no_of_hours' => 'required|integer|min:1|max:40',
            'units' => 'required|integer|min:1|max:6',
            'no_of_students' => 'required|integer|min:1',
            'year_section' => 'required|string|max:50',
            'action_type' => 'required|in:Examination,Laboratory,Lecture',
        ]);

        Faculty::create($validated);

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Faculty information added successfully!');
    }

    public function edit(Faculty $faculty)
    {
        return view('admin.faculties.edit', compact('faculty'));
    }

    public function update(Request $request, Faculty $faculty)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_subject' => 'required|string|max:255',
            'no_of_hours' => 'required|integer|min:1|max:40',
            'units' => 'required|integer|min:1|max:6',
            'no_of_students' => 'required|integer|min:1',
            'year_section' => 'required|string|max:50',
            'action_type' => 'required|in:Examination,Laboratory,Lecture',
        ]);

        $faculty->update($validated);

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Faculty information updated successfully!');
    }

    public function destroy(Faculty $faculty)
    {
        $faculty->delete();

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Faculty information deleted successfully!');
    }
}

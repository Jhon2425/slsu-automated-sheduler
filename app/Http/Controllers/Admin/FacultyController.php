<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\Program;
use App\Models\User;
use App\Models\FacultySubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class FacultyController extends Controller
{
    /**
     * Display all faculties
     */
    public function index()
    {
        $faculties = Faculty::with(['user', 'facultySubjects.subject'])
            ->get()
            ->map(function ($faculty) {
                $faculty->subjects_count = $faculty->facultySubjects->count();
                return $faculty;
            });

        return view('admin.faculty.index', compact('faculties'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $subjects = Subject::all();
        $programs = Program::all();

        return view('admin.faculty.create', compact('subjects', 'programs'));
    }

    /**
     * Store new faculty
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'civil_status'       => 'required|string',
            'birthdate'          => 'required|date',
            'employment_status'  => 'required|string',
            'home_address'       => 'required|string',
            'degree_earned'      => 'required|string',
            'year_graduated'     => 'required|integer',
            'course'             => 'required|string',
            'school_graduated'   => 'required|string',
            'subjects'           => 'nullable|array',
            'subjects.*.subject_id' => 'required_with:subjects|exists:subjects,id',
            'subjects.*.program_id' => 'nullable|exists:programs,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0',
            'subjects.*.year_level' => 'nullable|integer|min:1|max:4',
            'subjects.*.semester' => 'nullable|integer|min:1|max:2',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1️⃣ Create User
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // 2️⃣ Create Faculty Profile
                $faculty = Faculty::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'civil_status' => $request->civil_status,
                    'birthdate' => $request->birthdate,
                    'employment_status' => $request->employment_status,
                    'home_address' => $request->home_address,
                    'degree_earned' => $request->degree_earned,
                    'year_graduated' => $request->year_graduated,
                    'course' => $request->course,
                    'school_graduated' => $request->school_graduated,
                ]);

                // 3️⃣ Assign Subjects using FacultySubject model
                if ($request->filled('subjects')) {
                    foreach ($request->subjects as $sub) {
                        FacultySubject::create([
                            'faculty_id' => $faculty->id,
                            'subject_id' => $sub['subject_id'],
                            'program_id' => $sub['program_id'] ?? null,
                            'lecture_units' => $sub['lecture_units'] ?? 0,
                            'laboratory_units' => $sub['laboratory_units'] ?? 0,
                            'year_level' => $sub['year_level'] ?? null,
                            'semester' => $sub['semester'] ?? null,
                        ]);
                    }
                }
            });

            return redirect()->route('admin.faculty.index')
                ->with('success', 'Faculty created successfully!');
        } catch (\Exception $e) {
            Log::error('Faculty creation error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create faculty: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(Faculty $faculty)
    {
        $faculty->load(['facultySubjects.subject', 'user']);

        $subjects = Subject::all();
        $programs = Program::all();

        return view('admin.faculty.edit', [
            'faculty' => $faculty,
            'subjects' => $subjects,
            'programs' => $programs,
        ]);
    }

    /**
     * Update faculty info & subjects
     */
    public function update(Request $request, Faculty $faculty)
    {
        $request->validate([
            'subjects' => 'nullable|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.program_id' => 'nullable|exists:programs,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0',
            'subjects.*.year_level' => 'nullable|integer|min:1|max:4',
            'subjects.*.semester' => 'nullable|integer|min:1|max:2',
        ]);

        try {
            DB::transaction(function () use ($request, $faculty) {

                // Delete existing assignments
                FacultySubject::where('faculty_id', $faculty->id)->delete();

                // Create new assignments
                if ($request->filled('subjects')) {
                    foreach ($request->subjects as $sub) {
                        FacultySubject::create([
                            'faculty_id' => $faculty->id,
                            'subject_id' => $sub['subject_id'],
                            'program_id' => $sub['program_id'] ?? null,
                            'lecture_units' => $sub['lecture_units'] ?? 0,
                            'laboratory_units' => $sub['laboratory_units'] ?? 0,
                            'year_level' => $sub['year_level'] ?? null,
                            'semester' => $sub['semester'] ?? null,
                        ]);
                    }
                }
            });

            return redirect()->route('admin.faculty.edit', $faculty->id)
                ->with('success', 'Faculty subjects updated successfully!');
        } catch (\Exception $e) {
            Log::error('Faculty update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update faculty subjects: ' . $e->getMessage());
        }
    }

    /**
     * Delete faculty & related subjects
     */
    public function destroy(Faculty $faculty)
    {
        try {
            DB::transaction(function () use ($faculty) {
                // Delete faculty-subject assignments
                FacultySubject::where('faculty_id', $faculty->id)->delete();
                
                // Delete user if exists
                if ($faculty->user) {
                    $faculty->user->delete();
                }
                
                // Delete faculty
                $faculty->delete();
            });

            return redirect()->route('admin.faculty.index')
                ->with('success', 'Faculty deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Faculty deletion error: ' . $e->getMessage());
            return redirect()->route('admin.faculty.index')
                ->with('error', 'Failed to delete faculty: ' . $e->getMessage());
        }
    }

    /**
     * Fetch all subjects and assigned subjects for a faculty
     */
    public function getSubjects(Faculty $faculty)
    {
        try {
            $allSubjects = Subject::orderBy('subject_name')->get();
            $assignedSubjects = FacultySubject::where('faculty_id', $faculty->id)
                ->pluck('subject_id')
                ->toArray();

            return response()->json([
                'success' => true,
                'subjects' => $allSubjects,
                'assignedSubjects' => $assignedSubjects
            ]);
        } catch (\Exception $e) {
            Log::error('Get subjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects'
            ], 500);
        }
    }

    /**
     * Fetch detailed assigned subjects
     */
    public function getAssignedSubjects(Faculty $faculty)
    {
        try {
            $assignedSubjects = FacultySubject::where('faculty_id', $faculty->id)
                ->with('subject')
                ->get()
                ->map(function ($fs) {
                    return [
                        'id' => $fs->subject->id,
                        'subject_name' => $fs->subject->subject_name,
                        'course_code' => $fs->subject->course_code,
                        'units' => $fs->subject->units,
                        'semester' => $fs->semester ?? $fs->subject->semester,
                        'year_level' => $fs->year_level ?? $fs->subject->year_level,
                        'lecture_units' => $fs->lecture_units,
                        'laboratory_units' => $fs->laboratory_units,
                    ];
                });

            return response()->json([
                'success' => true,
                'subjects' => $assignedSubjects
            ]);
        } catch (\Exception $e) {
            Log::error('Get assigned subjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assigned subjects'
            ], 500);
        }
    }

    /**
     * Assign subjects via AJAX
     */
    public function assignSubjects(Request $request, Faculty $faculty)
    {
        $request->validate([
            'subjects' => 'nullable|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.program_id' => 'nullable|exists:programs,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0',
            'subjects.*.year_level' => 'nullable|integer|min:1|max:4',
            'subjects.*.semester' => 'nullable|integer|min:1|max:2',
        ]);

        try {
            DB::transaction(function () use ($request, $faculty) {
                // Delete existing assignments
                FacultySubject::where('faculty_id', $faculty->id)->delete();

                // Create new assignments
                if ($request->filled('subjects')) {
                    foreach ($request->subjects as $sub) {
                        FacultySubject::create([
                            'faculty_id' => $faculty->id,
                            'subject_id' => $sub['subject_id'],
                            'program_id' => $sub['program_id'] ?? null,
                            'lecture_units' => $sub['lecture_units'] ?? 0,
                            'laboratory_units' => $sub['laboratory_units'] ?? 0,
                            'year_level' => $sub['year_level'] ?? null,
                            'semester' => $sub['semester'] ?? null,
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Subjects assigned successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Assign subjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign subjects: ' . $e->getMessage()
            ], 500);
        }
    }
}
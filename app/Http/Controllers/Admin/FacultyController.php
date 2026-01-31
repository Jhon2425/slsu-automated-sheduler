<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\FacultySubject;
use App\Models\Program;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FacultyController extends Controller
{
    /**
     * Display faculty list
     */
    public function index()
    {
        $faculties = User::whereHas('faculty')
            ->with([
                'faculty' => function ($q) {
                    $q->withCount('facultySubjects');
                }
            ])
            ->get();

        return view('admin.faculty.index', compact('faculties'));
    }

    /**
     * Show the create form
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

            'subjects'                       => 'nullable|array',
            'subjects.*.subject_id'          => 'required_with:subjects|exists:subjects,id',
            'subjects.*.program_id'          => 'nullable|exists:programs,id',
            'subjects.*.lecture_units'       => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            // 1️⃣ Create User
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2️⃣ Create Faculty Profile
            $faculty = Faculty::create([
                'user_id'            => $user->id,
                'name'               => $request->name, // fixed for "Field 'name' doesn't have a default value"
                'civil_status'       => $request->civil_status,
                'birthdate'          => $request->birthdate,
                'employment_status'  => $request->employment_status,
                'home_address'       => $request->home_address,
                'degree_earned'      => $request->degree_earned,
                'year_graduated'     => $request->year_graduated,
                'course'             => $request->course,
                'school_graduated'   => $request->school_graduated,
            ]);

            // 3️⃣ Assign Subjects (optional)
            if ($request->filled('subjects')) {
                foreach ($request->subjects as $subject) {
                    FacultySubject::create([
                        'faculty_id'       => $faculty->id,
                        'subject_id'       => $subject['subject_id'],
                        'program_id'       => $subject['program_id'] ?? null,
                        'lecture_units'    => $subject['lecture_units'] ?? 0,
                        'laboratory_units' => $subject['laboratory_units'] ?? 0,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.faculty.index')
            ->with('success', 'Faculty created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(User $faculty)
    {
        $faculty->load([
            'faculty.facultySubjects.subject',
            'faculty.facultySubjects.program',
        ]);

        $subjects = Subject::all();
        $programs = Program::all();

        return view('admin.faculty.edit', [
            'faculty'          => $faculty,
            'subjects'         => $subjects,
            'programs'         => $programs,
            'existingSubjects' => $faculty->faculty->facultySubjects,
        ]);
    }

    /**
     * Update faculty subjects
     */
    public function update(Request $request, User $faculty)
    {
        $request->validate([
            'subjects' => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.program_id' => 'nullable|exists:programs,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0',
        ]);

        $facultyProfile = $faculty->faculty;

        DB::transaction(function () use ($request, $facultyProfile) {
            FacultySubject::where('faculty_id', $facultyProfile->id)->delete();

            foreach ($request->subjects as $data) {
                FacultySubject::create([
                    'faculty_id'       => $facultyProfile->id,
                    'subject_id'       => $data['subject_id'],
                    'program_id'       => $data['program_id'] ?? null,
                    'lecture_units'    => $data['lecture_units'] ?? 0,
                    'laboratory_units' => $data['laboratory_units'] ?? 0,
                ]);
            }
        });

        return redirect()
            ->route('admin.faculty.edit', $faculty->id)
            ->with('success', 'Faculty subjects updated successfully!');
    }

    /**
     * Delete faculty member
     */
    public function destroy(User $faculty)
    {
        try {
            DB::transaction(function () use ($faculty) {
                // Delete faculty profile and related data
                if ($faculty->faculty) {
                    // Delete faculty subjects
                    FacultySubject::where('faculty_id', $faculty->faculty->id)->delete();
                    
                    // Delete faculty profile
                    $faculty->faculty->delete();
                }
                
                // Delete user
                $faculty->delete();
            });

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty member deleted successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.faculty.index')
                ->with('error', 'Failed to delete faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Get all subjects for assignment modal (with current assignments marked)
     */
    public function getSubjects($id)
    {
        try {
            $user = User::findOrFail($id);
            $facultyProfile = $user->faculty;

            if (!$facultyProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty profile not found'
                ], 404);
            }

            // Get all subjects
            $subjects = Subject::orderBy('subject_name')->get();

            // Get IDs of subjects already assigned to this faculty
            $assignedSubjects = $facultyProfile->facultySubjects()
                ->pluck('subject_id')
                ->toArray();

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'assignedSubjects' => $assignedSubjects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assigned subjects for viewing only
     */
    public function getAssignedSubjects($id)
    {
        try {
            $user = User::findOrFail($id);
            $facultyProfile = $user->faculty;

            if (!$facultyProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty profile not found'
                ], 404);
            }

            // Get subjects assigned to this faculty with their details
            $subjects = $facultyProfile->facultySubjects()
                ->with('subject')
                ->get()
                ->map(function ($facultySubject) {
                    $subject = $facultySubject->subject;
                    return [
                        'id' => $subject->id,
                        'subject_name' => $subject->subject_name,
                        'course_code' => $subject->course_code,
                        'units' => $subject->units,
                        'semester' => $subject->semester,
                        'year_level' => $subject->year_level,
                        'enrolled_student' => $subject->enrolled_student ?? 0,
                        'lecture_units' => $facultySubject->lecture_units,
                        'laboratory_units' => $facultySubject->laboratory_units,
                    ];
                });

            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assigned subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign subjects to faculty (simplified version for modal)
     */
    public function assignSubjects(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $facultyProfile = $user->faculty;

            if (!$facultyProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty profile not found'
                ], 404);
            }

            // Validate the request
            $request->validate([
                'subjects' => 'nullable|array',
                'subjects.*' => 'exists:subjects,id'
            ]);

            DB::transaction(function () use ($request, $facultyProfile) {
                // Delete existing assignments
                FacultySubject::where('faculty_id', $facultyProfile->id)->delete();

                // Add new assignments
                if ($request->filled('subjects')) {
                    foreach ($request->subjects as $subjectId) {
                        FacultySubject::create([
                            'faculty_id' => $facultyProfile->id,
                            'subject_id' => $subjectId,
                            'program_id' => null,
                            'lecture_units' => 0,
                            'laboratory_units' => 0,
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Subjects assigned successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign subjects: ' . $e->getMessage()
            ], 500);
        }
    }
}
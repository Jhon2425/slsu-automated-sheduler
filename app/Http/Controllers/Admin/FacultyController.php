<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\FacultySubject;
use App\Models\FacultyUnavailability;
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
            'subjects.*.year_level'          => 'nullable|integer|min:1|max:4',
            'subjects.*.semester'            => 'nullable|string',

            'unavailabilities'               => 'nullable|array',
            'unavailabilities.*.day'         => 'required_with:unavailabilities|string',
            'unavailabilities.*.time_from'   => 'required_with:unavailabilities|string',
            'unavailabilities.*.time_to'     => 'required_with:unavailabilities|string',
            'unavailabilities.*.reason'      => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1️⃣ Create User
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id'  => 2, // Faculty role
                ]);

                // 2️⃣ Create Faculty Profile
                $faculty = Faculty::create([
                    'user_id'            => $user->id,
                    'name'               => $request->name,
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
                            'year_level'       => $subject['year_level'] ?? null,
                            'semester'         => $subject['semester'] ?? null,
                        ]);
                    }
                }

                // 4️⃣ Add Unavailabilities (optional)
                if ($request->filled('unavailabilities')) {
                    foreach ($request->unavailabilities as $unavail) {
                        FacultyUnavailability::create([
                            'faculty_id' => $faculty->id,
                            'day'        => $unavail['day'],
                            'time_from'  => $unavail['time_from'],
                            'time_to'    => $unavail['time_to'],
                            'reason'     => $unavail['reason'] ?? null,
                        ]);
                    }
                }
            });

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty created successfully!');

        } catch (\Exception $e) {
            // Catch any DB or validation errors
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create faculty: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(User $faculty)
    {
        // Load faculty data with subjects and unavailabilities
        $faculty->load([
            'faculty.facultySubjects.subject',
            'faculty.unavailabilities'
        ]);

        // Get all available subjects and programs
        $subjects = Subject::all();
        $programs = Program::all();

        // Transform faculty subjects for easier frontend consumption
        $facultySubjects = $faculty->faculty->facultySubjects->map(function($facultySubject) {
            return [
                'id' => $facultySubject->subject->id,
                'subject_name' => $facultySubject->subject->subject_name,
                'course_code' => $facultySubject->subject->course_code,
                'year_level' => $facultySubject->year_level ?? $facultySubject->subject->year_level,
                'semester' => $facultySubject->semester ?? $facultySubject->subject->semester,
                'lec' => $facultySubject->lecture_units ?? $facultySubject->subject->lec,
                'lab' => $facultySubject->laboratory_units ?? $facultySubject->subject->lab,
                'pre_req' => $facultySubject->subject->pre_req,
                'program_id' => $facultySubject->program_id ?? $facultySubject->subject->program_id ?? null,
                'pivot' => [
                    'id' => $facultySubject->id,
                    'lecture_units' => $facultySubject->lecture_units,
                    'laboratory_units' => $facultySubject->laboratory_units,
                    'year_level' => $facultySubject->year_level,
                    'semester' => $facultySubject->semester,
                ]
            ];
        });

        return view('admin.faculty.edit', [
            'user' => $faculty, // Pass the User model for the route
            'faculty' => $faculty->faculty, // Pass the Faculty model for form data
            'subjects' => $subjects,
            'programs' => $programs,
            'facultySubjects' => $facultySubjects,
            'unavailabilities' => $faculty->faculty->unavailabilities
        ]);
    }

    /**
     * Update faculty
     */
    public function update(Request $request, User $faculty)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $faculty->id,
            'password' => 'nullable|confirmed|min:8',

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
            'subjects.*.year_level'          => 'nullable|integer|min:1|max:4',
            'subjects.*.semester'            => 'nullable|string',

            'unavailabilities'               => 'nullable|array',
            'unavailabilities.*.day'         => 'required_with:unavailabilities|string',
            'unavailabilities.*.time_from'   => 'required_with:unavailabilities|string',
            'unavailabilities.*.time_to'     => 'required_with:unavailabilities|string',
            'unavailabilities.*.reason'      => 'nullable|string',
        ]);

        $facultyProfile = $faculty->faculty;

        try {
            DB::transaction(function () use ($request, $faculty, $facultyProfile) {
                
                // 1️⃣ Update User
                $userData = [
                    'name'  => $request->name,
                    'email' => $request->email,
                ];
                
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                
                $faculty->update($userData);

                // 2️⃣ Update Faculty Profile
                $facultyProfile->update([
                    'name'               => $request->name,
                    'civil_status'       => $request->civil_status,
                    'birthdate'          => $request->birthdate,
                    'employment_status'  => $request->employment_status,
                    'home_address'       => $request->home_address,
                    'degree_earned'      => $request->degree_earned,
                    'year_graduated'     => $request->year_graduated,
                    'course'             => $request->course,
                    'school_graduated'   => $request->school_graduated,
                ]);

                // 3️⃣ Update Subjects - Delete old and create new
                FacultySubject::where('faculty_id', $facultyProfile->id)->delete();
                
                if ($request->filled('subjects')) {
                    foreach ($request->subjects as $subject) {
                        FacultySubject::create([
                            'faculty_id'       => $facultyProfile->id,
                            'subject_id'       => $subject['subject_id'],
                            'program_id'       => $subject['program_id'] ?? null,
                            'lecture_units'    => $subject['lecture_units'] ?? 0,
                            'laboratory_units' => $subject['laboratory_units'] ?? 0,
                            'year_level'       => $subject['year_level'] ?? null,
                            'semester'         => $subject['semester'] ?? null,
                        ]);
                    }
                }

                // 4️⃣ Update Unavailabilities - Delete old and create new
                FacultyUnavailability::where('faculty_id', $facultyProfile->id)->delete();
                
                if ($request->filled('unavailabilities')) {
                    foreach ($request->unavailabilities as $unavail) {
                        FacultyUnavailability::create([
                            'faculty_id' => $facultyProfile->id,
                            'day'        => $unavail['day'],
                            'time_from'  => $unavail['time_from'],
                            'time_to'    => $unavail['time_to'],
                            'reason'     => $unavail['reason'] ?? null,
                        ]);
                    }
                }
            });

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty updated successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update faculty: ' . $e->getMessage());
        }
    }

    /**
     * Delete faculty member
     */
    public function destroy(User $faculty)
    {
        try {
            DB::transaction(function () use ($faculty) {
                if ($faculty->faculty) {
                    // Delete related subjects
                    FacultySubject::where('faculty_id', $faculty->faculty->id)->delete();
                    // Delete unavailabilities
                    FacultyUnavailability::where('faculty_id', $faculty->faculty->id)->delete();
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
     * Fetch all subjects (for modal)
     */
    public function getSubjects($id)
    {
        $user = User::findOrFail($id);
        $facultyProfile = $user->faculty;

        if (!$facultyProfile) {
            return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
        }

        $subjects = Subject::orderBy('subject_name')->get();
        $assignedSubjects = $facultyProfile->facultySubjects()->pluck('subject_id')->toArray();

        return response()->json([
            'success' => true,
            'subjects' => $subjects,
            'assignedSubjects' => $assignedSubjects
        ]);
    }

    /**
     * Get assigned subjects with details
     */
    public function getAssignedSubjects($id)
    {
        $user = User::findOrFail($id);
        $facultyProfile = $user->faculty;

        if (!$facultyProfile) {
            return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
        }

        $subjects = $facultyProfile->facultySubjects()
            ->with('subject')
            ->get()
            ->map(fn($fs) => [
                'id' => $fs->subject->id,
                'subject_name' => $fs->subject->subject_name,
                'course_code' => $fs->subject->course_code,
                'units' => $fs->subject->units,
                'semester' => $fs->subject->semester,
                'year_level' => $fs->subject->year_level,
                'enrolled_student' => $fs->subject->enrolled_student ?? 0,
                'lecture_units' => $fs->lecture_units,
                'laboratory_units' => $fs->laboratory_units,
            ]);

        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    /**
     * Assign subjects (simplified)
     */
    public function assignSubjects(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $facultyProfile = $user->faculty;

        if (!$facultyProfile) {
            return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
        }

        $request->validate([
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id'
        ]);

        try {
            DB::transaction(function () use ($request, $facultyProfile) {
                // Delete old assignments
                FacultySubject::where('faculty_id', $facultyProfile->id)->delete();

                // Add new ones
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

            return response()->json(['success' => true, 'message' => 'Subjects assigned successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign subjects: ' . $e->getMessage()], 500);
        }
    }
}
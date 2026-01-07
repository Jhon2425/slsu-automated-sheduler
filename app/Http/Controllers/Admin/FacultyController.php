<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\Program;
use App\Models\FacultySubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class FacultyController extends Controller
{
    /**
     * Get faculty role id
     */
    private function facultyRoleId(): int
    {
        return DB::table('roles')->where('name', 'faculty')->value('id');
    }

    /**
     * Display faculty list
     */
    public function index()
    {
        $faculties = User::where('role_id', $this->facultyRoleId())
            ->with(['faculty.facultySubjects.subject', 'faculty.facultySubjects.program'])
            ->orderBy('name')
            ->get();

        return view('admin.faculty.index', compact('faculties'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $subjects = Subject::orderBy('year_level')
            ->orderBy('semester')
            ->orderBy('subject_name')
            ->get();

        $programs = Program::orderBy('id')->get();

        return view('admin.faculty.create', compact('subjects', 'programs'));
    }

    /**
     * Store faculty
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'civil_status' => 'required|in:Single,Married,Widowed,Divorced',
            'birthdate' => 'required|date|before:today',
            'employment_status' => 'required|in:Full-Time,Part-Time,Contractual',
            'home_address' => 'required|string',

            'degree_earned' => 'required|in:Bachelor Degree,Master Degree,Doctorate Degree,Professional Degree',
            'year_graduated' => 'required|integer|min:1950|max:' . date('Y'),
            'course' => 'required|string|max:255',
            'school_graduated' => 'required|string|max:255',

            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            'subjects' => 'nullable|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0|max:10',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0|max:10',
            'subjects.*.program_id' => 'nullable|exists:programs,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $this->facultyRoleId(),
            ]);

            // Create faculty record
            $faculty = Faculty::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'civil_status' => $validated['civil_status'],
                'birthdate' => $validated['birthdate'],
                'employment_status' => $validated['employment_status'],
                'home_address' => $validated['home_address'],
                'degree_earned' => $validated['degree_earned'],
                'year_graduated' => $validated['year_graduated'],
                'course' => $validated['course'],
                'school_graduated' => $validated['school_graduated'],
            ]);

            // Assign subjects if provided
            if ($request->has('subjects')) {
                foreach ($request->subjects as $subjectData) {
                    if (!empty($subjectData['subject_id'])) {
                        FacultySubject::create([
                            'faculty_id' => $user->id, // ✅ USERS.ID
                            'subject_id' => $subjectData['subject_id'],
                            'program_id' => $subjectData['program_id'] ?? null,
                            'lecture_units' => $subjectData['lecture_units'] ?? null,
                            'laboratory_units' => $subjectData['laboratory_units'] ?? null,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.faculty.index')
            ->with('success', 'Faculty member created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(User $faculty)
    {
        $this->ensureFaculty($faculty);

        // If missing, create a default faculty record to prevent errors
        $facultyRecord = $faculty->faculty ?? $this->createDefaultFacultyRecord($faculty);

        $subjects = Subject::orderBy('year_level')
            ->orderBy('semester')
            ->orderBy('subject_name')
            ->get();

        $programs = Program::orderBy('id')->get();

        $assignedSubjects = $facultyRecord->facultySubjects()
            ->with('subject', 'program')
            ->get();

        return view('admin.faculty.edit', compact(
            'faculty',
            'facultyRecord',
            'subjects',
            'programs',
            'assignedSubjects'
        ));
    }

    /**
     * Update faculty
     */
    public function update(Request $request, User $faculty)
    {
        $this->ensureFaculty($faculty);

        $facultyRecord = $faculty->faculty ?? $this->createDefaultFacultyRecord($faculty);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'civil_status' => 'required|in:Single,Married,Widowed,Divorced',
            'birthdate' => 'required|date|before:today',
            'employment_status' => 'required|in:Full-Time,Part-Time,Contractual',
            'home_address' => 'required|string',

            'degree_earned' => 'required|in:Bachelor Degree,Master Degree,Doctorate Degree,Professional Degree',
            'year_graduated' => 'required|integer|min:1950|max:' . date('Y'),
            'course' => 'required|string|max:255',
            'school_graduated' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email,' . $faculty->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],

            'subjects' => 'nullable|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0|max:10',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0|max:10',
            'subjects.*.program_id' => 'nullable|exists:programs,id',
        ]);

        DB::transaction(function () use ($validated, $request, $faculty, $facultyRecord) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $faculty->update($userData);

            $facultyRecord->update([
                'name' => $validated['name'],
                'civil_status' => $validated['civil_status'],
                'birthdate' => $validated['birthdate'],
                'employment_status' => $validated['employment_status'],
                'home_address' => $validated['home_address'],
                'degree_earned' => $validated['degree_earned'],
                'year_graduated' => $validated['year_graduated'],
                'course' => $validated['course'],
                'school_graduated' => $validated['school_graduated'],
            ]);

            // Reset assigned subjects (delete by USERS.ID)
            FacultySubject::where('faculty_id', $faculty->id)->delete();

            if ($request->has('subjects')) {
                foreach ($request->subjects as $subjectData) {
                    if (!empty($subjectData['subject_id'])) {
                        FacultySubject::create([
                            'faculty_id' => $faculty->id, // ✅ USERS.ID
                            'subject_id' => $subjectData['subject_id'],
                            'program_id' => $subjectData['program_id'] ?? null,
                            'lecture_units' => $subjectData['lecture_units'] ?? null,
                            'laboratory_units' => $subjectData['laboratory_units'] ?? null,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.faculty.index')
            ->with('success', 'Faculty member updated successfully.');
    }

    /**
     * Delete faculty
     */
    public function destroy(User $faculty)
    {
        $this->ensureFaculty($faculty);

        $facultyRecord = $faculty->faculty;

        if ($facultyRecord) {
            DB::transaction(function () use ($faculty, $facultyRecord) {
                // Delete all subjects by USERS.ID
                FacultySubject::where('faculty_id', $faculty->id)->delete();
                $facultyRecord->delete();
                $faculty->delete();
            });

            return redirect()->route('admin.faculty.index')
                ->with('success', 'Faculty member deleted successfully.');
        }

        return redirect()->route('admin.faculty.index')
            ->with('error', 'Faculty record not found.');
    }

    /**
     * Ensure user is faculty
     */
    private function ensureFaculty(User $faculty): void
    {
        if ($faculty->role_id !== $this->facultyRoleId()) {
            abort(404);
        }
    }

    /**
     * Create a default faculty record with safe default values
     */
    private function createDefaultFacultyRecord(User $user): Faculty
    {
        return Faculty::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'civil_status' => 'Single',
            'birthdate' => now()->subYears(30), // default 30 years ago
            'employment_status' => 'Full-Time',
            'home_address' => 'N/A',
            'degree_earned' => 'Bachelor Degree',
            'year_graduated' => 2010,
            'course' => 'N/A',
            'school_graduated' => 'N/A',
        ]);
    }
}

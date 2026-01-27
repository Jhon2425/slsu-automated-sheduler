<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use App\Models\FacultySubject;
use App\Models\Subject;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculty members
     */
    public function index()
    {
        $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');
        
        $faculties = User::where('role_id', $facultyRoleId)
            ->with(['faculty', 'faculty.facultySubjects.subject'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.faculty.index', compact('faculties'));
    }

    /**
     * Show the form for creating a new faculty member
     */
    public function create()
    {
        $subjects = Subject::orderBy('subject_name')->get();
        
        // Try to get programs - handle if table/column doesn't exist
        try {
            $programs = Program::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::warning('Could not load programs: ' . $e->getMessage());
            $programs = collect(); // Empty collection
        }
        
        return view('admin.faculty.create', compact('subjects', 'programs'));
    }

    /**
     * Store a newly created faculty member
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'civil_status' => 'nullable|string',
            'birthdate' => 'nullable|date',
            'employment_status' => 'nullable|string',
            'home_address' => 'nullable|string',
            'degree_earned' => 'nullable|string',
            'year_graduated' => 'nullable|string',
            'course' => 'nullable|string',
            'school_graduated' => 'nullable|string',
            'subjects.*.subject_id' => 'nullable|exists:subjects,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get faculty role
            $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');

            if (!$facultyRoleId) {
                throw new \Exception('Faculty role not found');
            }

            // Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $facultyRoleId,
            ]);

            // Create Faculty profile
            Faculty::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'civil_status' => $request->civil_status,
                'birthdate' => $request->birthdate,
                'employment_status' => $request->employment_status,
                'home_address' => $request->home_address,
                'degree_earned' => $request->degree_earned,
                'year_graduated' => $request->year_graduated,
                'course' => $request->course,
                'school_graduated' => $request->school_graduated,
            ]);

            // Assign subjects if provided - WITH DUPLICATE CHECK
            $assignedSubjects = [];
            
            if ($request->has('subjects')) {
                foreach ($request->subjects as $subjectData) {
                    if (!empty($subjectData['subject_id'])) {
                        $subjectId = $subjectData['subject_id'];
                        
                        // Skip if already assigned in this request
                        if (in_array($subjectId, $assignedSubjects)) {
                            Log::warning("Duplicate subject assignment skipped", [
                                'faculty_id' => $user->id,
                                'subject_id' => $subjectId
                            ]);
                            continue;
                        }
                        
                        // Check if already exists in database
                        $exists = FacultySubject::where('faculty_id', $user->id)
                            ->where('subject_id', $subjectId)
                            ->exists();
                        
                        if ($exists) {
                            Log::warning("Subject already assigned to faculty", [
                                'faculty_id' => $user->id,
                                'subject_id' => $subjectId
                            ]);
                            continue;
                        }
                        
                        // Create the assignment
                        FacultySubject::create([
                            'faculty_id' => $user->id,
                            'subject_id' => $subjectId,
                            'program_id' => $subjectData['program_id'] ?? null,
                            'lecture_units' => $subjectData['lecture_units'] ?? 0,
                            'laboratory_units' => $subjectData['laboratory_units'] ?? 0,
                        ]);
                        
                        // Mark as assigned
                        $assignedSubjects[] = $subjectId;
                    }
                }
            }

            DB::commit();

            Log::info('Faculty created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'subjects_assigned' => count($assignedSubjects)
            ]);

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty member created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating faculty', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating faculty: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified faculty member
     */
    public function edit($id)
    {
        $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');
        
        $faculty = User::where('role_id', $facultyRoleId)
            ->with(['faculty.facultySubjects.subject'])
            ->findOrFail($id);

        $subjects = Subject::orderBy('subject_name')->get();
        
        // Try to get programs - handle if table/column doesn't exist
        try {
            $programs = Program::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::warning('Could not load programs: ' . $e->getMessage());
            $programs = collect(); // Empty collection
        }
        
        // Debug log to check what's being loaded
        Log::info('Faculty Edit - Loading subjects', [
            'faculty_id' => $id,
            'faculty_subjects_count' => $faculty->faculty->facultySubjects->count(),
            'faculty_subjects' => $faculty->faculty->facultySubjects->toArray()
        ]);
        
        return view('admin.faculty.edit', compact('faculty', 'subjects', 'programs'));
    }

    /**
     * Update the specified faculty member
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'civil_status' => 'nullable|string',
            'birthdate' => 'nullable|date',
            'employment_status' => 'nullable|string',
            'home_address' => 'nullable|string',
            'degree_earned' => 'nullable|string',
            'year_graduated' => 'nullable|string',
            'course' => 'nullable|string',
            'school_graduated' => 'nullable|string',
            'subjects.*.subject_id' => 'nullable|exists:subjects,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            
            // Update user
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($validated['password'])
                ]);
            }

            // Update faculty profile
            $user->faculty->update([
                'name' => $validated['name'],
                'civil_status' => $request->civil_status,
                'birthdate' => $request->birthdate,
                'employment_status' => $request->employment_status,
                'home_address' => $request->home_address,
                'degree_earned' => $request->degree_earned,
                'year_graduated' => $request->year_graduated,
                'course' => $request->course,
                'school_graduated' => $request->school_graduated,
            ]);

            // Update subject assignments if provided
            if ($request->has('subjects')) {
                // Clear existing assignments
                FacultySubject::where('faculty_id', $user->id)->delete();
                
                // Assign new subjects - WITH DUPLICATE CHECK
                $assignedSubjects = [];
                $createdCount = 0;

                foreach ($request->subjects as $subjectData) {
                    if (!empty($subjectData['subject_id'])) {
                        $subjectId = $subjectData['subject_id'];
                        
                        // Skip if already assigned in this request
                        if (in_array($subjectId, $assignedSubjects)) {
                            Log::warning("Duplicate subject assignment skipped during update", [
                                'faculty_id' => $user->id,
                                'subject_id' => $subjectId
                            ]);
                            continue;
                        }

                        FacultySubject::create([
                            'faculty_id' => $user->id,
                            'subject_id' => $subjectId,
                            'program_id' => $subjectData['program_id'] ?? null,
                            'lecture_units' => $subjectData['lecture_units'] ?? 0,
                            'laboratory_units' => $subjectData['laboratory_units'] ?? 0,
                        ]);

                        $assignedSubjects[] = $subjectId;
                        $createdCount++;
                    }
                }

                Log::info('Subject assignments updated', [
                    'faculty_id' => $user->id,
                    'subjects_assigned' => $createdCount
                ]);
            }

            DB::commit();

            Log::info('Faculty updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'subjects_updated' => isset($createdCount) ? $createdCount : 0
            ]);

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty member updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating faculty', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating faculty: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified faculty member
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            
            // Delete related records first
            if ($user->faculty) {
                FacultySubject::where('faculty_id', $user->id)->delete();
                $user->faculty->delete();
            }
            
            // Delete user
            $user->delete();

            DB::commit();

            Log::info('Faculty deleted successfully', [
                'user_id' => $id
            ]);

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty member deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting faculty', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error deleting faculty: ' . $e->getMessage());
        }
    }

    /**
     * Get subjects for a specific faculty member (AJAX)
     */
    public function getSubjects($id)
    {
        try {
            $facultySubjects = FacultySubject::where('faculty_id', $id)
                ->with('subject')
                ->get();

            return response()->json([
                'success' => true,
                'subjects' => $facultySubjects
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting faculty subjects', [
                'faculty_id' => $id,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading subjects'
            ], 500);
        }
    }

    /**
     * Assign subjects to faculty member (AJAX)
     */
    public function assignSubjects(Request $request, $id)
    {
        $validated = $request->validate([
            'subjects' => 'required|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.lecture_units' => 'nullable|numeric|min:0',
            'subjects.*.laboratory_units' => 'nullable|numeric|min:0',
            'subjects.*.program_id' => 'nullable|exists:programs,id',
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Clear existing assignments
            FacultySubject::where('faculty_id', $user->id)->delete();

            // Assign new subjects - WITH DUPLICATE CHECK
            $assignedSubjects = [];
            $createdCount = 0;

            foreach ($validated['subjects'] as $subjectData) {
                $subjectId = $subjectData['subject_id'];
                
                // Skip if already assigned in this request
                if (in_array($subjectId, $assignedSubjects)) {
                    Log::warning("Duplicate subject assignment skipped", [
                        'faculty_id' => $user->id,
                        'subject_id' => $subjectId
                    ]);
                    continue;
                }

                FacultySubject::create([
                    'faculty_id' => $user->id,
                    'subject_id' => $subjectId,
                    'program_id' => $subjectData['program_id'] ?? null,
                    'lecture_units' => $subjectData['lecture_units'] ?? 0,
                    'laboratory_units' => $subjectData['laboratory_units'] ?? 0,
                ]);

                $assignedSubjects[] = $subjectId;
                $createdCount++;
            }

            DB::commit();

            Log::info('Subjects assigned successfully', [
                'faculty_id' => $user->id,
                'subjects_assigned' => $createdCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned {$createdCount} subjects"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error assigning subjects', [
                'faculty_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error assigning subjects: ' . $e->getMessage()
            ], 500);
        }
    }
}
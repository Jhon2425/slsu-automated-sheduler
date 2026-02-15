<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\FacultySubject;
use App\Models\FacultyUnavailability;
use App\Models\EducationalBackground;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FacultyController extends Controller
{
    /**
     * Display faculty list
     */
    public function index()
    {
        $faculty = User::whereHas('faculty')
            ->with([
                'faculty' => function ($q) {
                    $q->withCount('facultySubjects');
                }
            ])
            ->get();

        return view('admin.faculty.index', compact('faculty'));
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
        // Validate incoming request
        try {
            $validated = $request->validate([
                'name'         => 'required|string|max:255',
                'faculty_code' => 'required|string|max:255|unique:faculty,faculty_code|unique:users,faculty_code',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|confirmed|min:8',

                'civil_status'       => 'required|string',
                'birthdate'          => 'required|date|before:today',
                'employment_status'  => 'required|string',
                'home_address'       => 'required|string',
                'years_of_service'   => 'required|integer|min:0',
                'rank'               => 'nullable|string|max:255',
                
                // Educational backgrounds - at least one required
                'education' => 'required|array|min:1',
                'education.*.degree_earned' => 'required|in:Bachelor Degree,Master Degree,Doctorate Degree,Professional Degree',
                'education.*.year_graduated' => 'required|integer|min:1950|max:' . date('Y'),
                'education.*.course' => 'required|string|max:255',
                'education.*.school_graduated' => 'required|string|max:255',

                // Subjects (optional)
                'subjects'                       => 'nullable|array',
                'subjects.*.subject_id'          => 'required_with:subjects|exists:subjects,id',
                'subjects.*.program_id'          => 'nullable|exists:programs,id',
                'subjects.*.lecture_units'       => 'nullable|numeric|min:0',
                'subjects.*.laboratory_units'    => 'nullable|numeric|min:0',
                'subjects.*.year_level'          => 'nullable|integer|min:1|max:4',
                'subjects.*.semester'            => 'nullable|string',
                'subjects.*.class_size'          => 'nullable|integer|min:0',

                // Unavailabilities (optional)
                'unavailabilities'               => 'nullable|array',
                'unavailabilities.*.day'         => 'required_with:unavailabilities|string',
                'unavailabilities.*.time_from'   => 'required_with:unavailabilities|date_format:H:i',
                'unavailabilities.*.time_to'     => 'required_with:unavailabilities|date_format:H:i|after:unavailabilities.*.time_from',
                'unavailabilities.*.reason'      => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Faculty validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            throw $e;
        }

        try {
            DB::beginTransaction();

            // 1️⃣ Create User Account with faculty_code
            $user = User::create([
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'faculty_code' => $validated['faculty_code'],
                'password'     => Hash::make($validated['password']),
                'role_id'      => 2, // Faculty role - ensure this exists in your roles table
            ]);

            Log::info('User created for faculty', [
                'user_id' => $user->id,
                'email' => $user->email,
                'faculty_code' => $user->faculty_code
            ]);

            // 2️⃣ Create Faculty Profile
            $faculty = Faculty::create([
                'user_id'            => $user->id,
                'faculty_code'       => $validated['faculty_code'],
                'name'               => $validated['name'],
                'civil_status'       => $validated['civil_status'],
                'birthdate'          => $validated['birthdate'],
                'employment_status'  => $validated['employment_status'],
                'home_address'       => $validated['home_address'],
                'years_of_service'   => $validated['years_of_service'],
                'rank'               => $validated['rank'] ?? null,
            ]);

            Log::info('Faculty profile created', ['faculty_id' => $faculty->id, 'faculty_code' => $faculty->faculty_code]);

            // 3️⃣ Create Educational Backgrounds with faculty_code
            $educationCount = 0;
            foreach ($validated['education'] as $education) {
                EducationalBackground::create([
                    'faculty_id'        => $faculty->id,
                    'faculty_code'      => $validated['faculty_code'],
                    'degree_earned'     => $education['degree_earned'],
                    'year_graduated'    => $education['year_graduated'],
                    'course'            => $education['course'],
                    'school_graduated'  => $education['school_graduated'],
                ]);
                $educationCount++;
            }

            Log::info('Educational backgrounds created', ['faculty_id' => $faculty->id, 'faculty_code' => $validated['faculty_code'], 'count' => $educationCount]);

            // 4️⃣ Assign Subjects (optional)
            $subjectCount = 0;
            if (!empty($validated['subjects'])) {
                foreach ($validated['subjects'] as $subject) {
                    // Get lecture and lab units with fallback to 0
                    $lectureUnits = $subject['lecture_units'] ?? 0;
                    $laboratoryUnits = $subject['laboratory_units'] ?? 0;

                    // Only create assignment if at least one unit type has a value
                    if ($lectureUnits > 0 || $laboratoryUnits > 0) {
                        FacultySubject::create([
                            'faculty_id'       => $faculty->id,
                            'faculty_code'     => $faculty->faculty_code,
                            'subject_id'       => $subject['subject_id'],
                            'program_id'       => $subject['program_id'] ?? null,
                            'lecture_units'    => $lectureUnits,
                            'laboratory_units' => $laboratoryUnits,
                            'year_level'       => $subject['year_level'] ?? null,
                            'semester'         => $subject['semester'] ?? null,
                            'class_size'       => $subject['class_size'] ?? 0,
                        ]);
                        $subjectCount++;
                    }
                }
            }

            Log::info('Subjects assigned', ['faculty_id' => $faculty->id, 'count' => $subjectCount]);

            // 5️⃣ Add Unavailabilities (optional)
            $unavailabilityCount = 0;
            if (!empty($validated['unavailabilities'])) {
                foreach ($validated['unavailabilities'] as $unavail) {
                    FacultyUnavailability::create([
                        'faculty_id'  => $faculty->id,
                        'faculty_code' => $faculty->faculty_code,
                        'day'         => $unavail['day'],
                        'time_from'   => $unavail['time_from'],
                        'time_to'     => $unavail['time_to'],
                        'reason'      => $unavail['reason'] ?? null,
                    ]);
                    $unavailabilityCount++;
                }
            }

            Log::info('Unavailabilities created', ['faculty_id' => $faculty->id, 'count' => $unavailabilityCount]);

            DB::commit();

            Log::info('Faculty successfully created', [
                'faculty_id' => $faculty->id,
                'user_id' => $user->id,
                'faculty_code' => $faculty->faculty_code
            ]);

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty member created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Comprehensive error logging
            Log::error('Faculty creation failed', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);

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
        // Load faculty data with educational backgrounds, subjects and unavailabilities
        $faculty->load([
            'faculty.educationalBackgrounds',
            'faculty.facultySubjects.subject',
            'faculty.unavailabilities'
        ]);

        // Get all available subjects and programs
        $subjects = Subject::all();
        $programs = Program::all();

        // Get the faculty profile
        $facultyProfile = $faculty->faculty;

        if (!$facultyProfile) {
            return redirect()
                ->route('admin.faculty.index')
                ->with('error', 'Faculty profile not found');
        }

        // Transform faculty subjects for easier frontend consumption
        $facultySubjects = $facultyProfile->facultySubjects->map(function($facultySubject) {
            return [
                'id' => $facultySubject->id, // FacultySubject ID for editing
                'subject_id' => $facultySubject->subject->id,
                'subject_name' => $facultySubject->subject->subject_name,
                'course_code' => $facultySubject->subject->course_code,
                'year_level' => $facultySubject->year_level ?? $facultySubject->subject->year_level,
                'semester' => $facultySubject->semester ?? $facultySubject->subject->semester,
                'lecture_units' => $facultySubject->lecture_units ?? $facultySubject->subject->lec ?? 0,
                'laboratory_units' => $facultySubject->laboratory_units ?? $facultySubject->subject->lab ?? 0,
                'lec' => $facultySubject->lecture_units ?? $facultySubject->subject->lec ?? 0, // For backward compatibility
                'lab' => $facultySubject->laboratory_units ?? $facultySubject->subject->lab ?? 0, // For backward compatibility
                'class_size' => $facultySubject->class_size ?? 0,
                'pre_req' => $facultySubject->subject->pre_req,
                'program_id' => $facultySubject->program_id ?? $facultySubject->subject->program_id ?? null,
            ];
        });

        // Transform unavailabilities for easier frontend consumption
        $unavailabilities = $facultyProfile->unavailabilities->map(function($unavail) {
            return [
                'id' => $unavail->id,
                'day' => $unavail->day,
                'time_from' => $unavail->time_from, // This will be in H:i format (e.g., "08:00")
                'time_to' => $unavail->time_to,     // This will be in H:i format (e.g., "10:00")
                'reason' => $unavail->reason ?? '',
            ];
        });

        // Get schedules for this faculty
        $schedules = Schedule::where('faculty_id', $faculty->id)
            ->with('subject')
            ->get()
            ->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject_name' => $schedule->subject->subject_name ?? 'N/A',
                    'course_code' => $schedule->subject->course_code ?? 'N/A',
                    'day' => $schedule->day,
                    'time_from' => $schedule->time_from,
                    'time_to' => $schedule->time_to,
                    'room' => $schedule->room ?? 'TBA',
                    'section' => $schedule->section ?? 'N/A',
                ];
            });

        // Assign the transformed data to the faculty profile
        $facultyProfile->subjects = $facultySubjects;
        $facultyProfile->unavailabilities = $unavailabilities;
        $facultyProfile->schedules = $schedules;

        return view('admin.faculty.edit', [
            'user' => $faculty,
            'faculty' => $facultyProfile,
            'subjects' => $subjects,
            'programs' => $programs,
        ]);
    }

    /**
     * Update faculty
     */
    public function update(Request $request, User $faculty)
    {
        $facultyProfile = $faculty->faculty;

        if (!$facultyProfile) {
            return redirect()
                ->route('admin.faculty.index')
                ->with('error', 'Faculty profile not found');
        }

        // Store old faculty_code before validation for schedule updates
        $oldFacultyCode = $facultyProfile->faculty_code;

        // Validate incoming request
        try {
            $validated = $request->validate([
                'name'         => 'required|string|max:255',
                'faculty_code' => 'required|string|max:255|unique:faculty,faculty_code,' . $facultyProfile->id . '|unique:users,faculty_code,' . $faculty->id,
                'email'        => 'required|email|unique:users,email,' . $faculty->id,
                'password'     => 'nullable|confirmed|min:8',

                'civil_status'       => 'required|string',
                'birthdate'          => 'required|date|before:today',
                'employment_status'  => 'required|string',
                'home_address'       => 'required|string',
                'years_of_service'   => 'required|integer|min:0',
                'rank'               => 'nullable|string|max:255',
                
                // Educational backgrounds - at least one required
                'education' => 'required|array|min:1',
                'education.*.id' => 'nullable|exists:educational_backgrounds,id',
                'education.*.degree_earned' => 'required|in:Bachelor Degree,Master Degree,Doctorate Degree,Professional Degree',
                'education.*.year_graduated' => 'required|integer|min:1950|max:' . date('Y'),
                'education.*.course' => 'required|string|max:255',
                'education.*.school_graduated' => 'required|string|max:255',

                // Subjects (optional)
                'subjects'                       => 'nullable|array',
                'subjects.*.subject_id'          => 'required_with:subjects|exists:subjects,id',
                'subjects.*.program_id'          => 'nullable|exists:programs,id',
                'subjects.*.lecture_units'       => 'nullable|numeric|min:0',
                'subjects.*.laboratory_units'    => 'nullable|numeric|min:0',
                'subjects.*.year_level'          => 'nullable|integer|min:1|max:4',
                'subjects.*.semester'            => 'nullable|string',
                'subjects.*.class_size'          => 'nullable|integer|min:0',

                // Unavailabilities (optional)
                'unavailabilities'               => 'nullable|array',
                'unavailabilities.*.day'         => 'required_with:unavailabilities|string',
                'unavailabilities.*.time_from'   => 'required_with:unavailabilities|date_format:H:i',
                'unavailabilities.*.time_to'     => 'required_with:unavailabilities|date_format:H:i|after:unavailabilities.*.time_from',
                'unavailabilities.*.reason'      => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Faculty update validation failed', [
                'faculty_id' => $facultyProfile->id,
                'errors' => $e->errors(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            throw $e;
        }

        try {
            DB::beginTransaction();
            
            // 1️⃣ Update User with faculty_code
            $userData = [
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'faculty_code' => $validated['faculty_code'],
            ];
            
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            
            $faculty->update($userData);

            Log::info('User updated for faculty', [
                'user_id' => $faculty->id,
                'faculty_id' => $facultyProfile->id,
                'faculty_code' => $validated['faculty_code']
            ]);

            // 2️⃣ Update Faculty Profile
            $facultyProfile->update([
                'faculty_code'       => $validated['faculty_code'],
                'name'               => $validated['name'],
                'civil_status'       => $validated['civil_status'],
                'birthdate'          => $validated['birthdate'],
                'employment_status'  => $validated['employment_status'],
                'home_address'       => $validated['home_address'],
                'years_of_service'   => $validated['years_of_service'],
                'rank'               => $validated['rank'] ?? null,
            ]);

            Log::info('Faculty profile updated', ['faculty_id' => $facultyProfile->id]);

            // 3️⃣ Handle Educational Backgrounds with updated faculty_code
            $existingEducationIds = [];
            $educationCount = 0;
            
            foreach ($validated['education'] as $educationData) {
                if (!empty($educationData['id'])) {
                    // Update existing educational background
                    $education = EducationalBackground::find($educationData['id']);
                    if ($education && $education->faculty_id === $facultyProfile->id) {
                        $education->update([
                            'faculty_code'      => $validated['faculty_code'],
                            'degree_earned'     => $educationData['degree_earned'],
                            'year_graduated'    => $educationData['year_graduated'],
                            'course'            => $educationData['course'],
                            'school_graduated'  => $educationData['school_graduated'],
                        ]);
                        $existingEducationIds[] = $educationData['id'];
                        $educationCount++;
                    }
                } else {
                    // Create new educational background
                    $newEducation = EducationalBackground::create([
                        'faculty_id'        => $facultyProfile->id,
                        'faculty_code'      => $validated['faculty_code'],
                        'degree_earned'     => $educationData['degree_earned'],
                        'year_graduated'    => $educationData['year_graduated'],
                        'course'            => $educationData['course'],
                        'school_graduated'  => $educationData['school_graduated'],
                    ]);
                    $existingEducationIds[] = $newEducation->id;
                    $educationCount++;
                }
            }

            // Delete educational backgrounds that were removed
            $deletedEducation = EducationalBackground::where('faculty_id', $facultyProfile->id)
                ->whereNotIn('id', $existingEducationIds)
                ->delete();

            Log::info('Educational backgrounds updated', [
                'faculty_id' => $facultyProfile->id,
                'faculty_code' => $validated['faculty_code'],
                'updated' => $educationCount,
                'deleted' => $deletedEducation
            ]);

            // 4️⃣ Update Subjects - Delete old and create new with updated faculty_code
            $deletedSubjects = FacultySubject::where('faculty_id', $facultyProfile->id)->delete();
            $subjectCount = 0;
            
            if (!empty($validated['subjects'])) {
                foreach ($validated['subjects'] as $subject) {
                    // Get lecture and lab units
                    $lectureUnits = $subject['lecture_units'] ?? 0;
                    $laboratoryUnits = $subject['laboratory_units'] ?? 0;

                    // Only create assignment if at least one unit type has a value
                    if ($lectureUnits > 0 || $laboratoryUnits > 0) {
                        FacultySubject::create([
                            'faculty_id'       => $facultyProfile->id,
                            'faculty_code'     => $validated['faculty_code'],
                            'subject_id'       => $subject['subject_id'],
                            'program_id'       => $subject['program_id'] ?? null,
                            'lecture_units'    => $lectureUnits,
                            'laboratory_units' => $laboratoryUnits,
                            'year_level'       => $subject['year_level'] ?? null,
                            'semester'         => $subject['semester'] ?? null,
                            'class_size'       => $subject['class_size'] ?? 0,
                        ]);
                        $subjectCount++;
                    }
                }
            }

            Log::info('Subjects updated', [
                'faculty_id' => $facultyProfile->id,
                'deleted' => $deletedSubjects,
                'created' => $subjectCount
            ]);

            // 5️⃣ Update Unavailabilities - Delete old and create new with updated faculty_code
            $deletedUnavailabilities = FacultyUnavailability::where('faculty_id', $facultyProfile->id)->delete();
            $unavailabilityCount = 0;
            
            if (!empty($validated['unavailabilities'])) {
                foreach ($validated['unavailabilities'] as $unavail) {
                    FacultyUnavailability::create([
                        'faculty_id'  => $facultyProfile->id,
                        'faculty_code' => $validated['faculty_code'],
                        'day'         => $unavail['day'],
                        'time_from'   => $unavail['time_from'],
                        'time_to'     => $unavail['time_to'],
                        'reason'      => $unavail['reason'] ?? null,
                    ]);
                    $unavailabilityCount++;
                }
            }

            Log::info('Unavailabilities updated', [
                'faculty_id' => $facultyProfile->id,
                'deleted' => $deletedUnavailabilities,
                'created' => $unavailabilityCount
            ]);

            // 6️⃣ Update Schedules with new faculty_code (if faculty_code changed)
            $scheduleUpdateCount = 0;
            if ($oldFacultyCode !== $validated['faculty_code']) {
                $scheduleUpdateCount = Schedule::where('faculty_id', $faculty->id)
                    ->where('faculty_code', $oldFacultyCode)
                    ->update(['faculty_code' => $validated['faculty_code']]);

                Log::info('Schedules updated with new faculty_code', [
                    'faculty_id' => $faculty->id,
                    'old_faculty_code' => $oldFacultyCode,
                    'new_faculty_code' => $validated['faculty_code'],
                    'schedules_updated' => $scheduleUpdateCount
                ]);
            }

            DB::commit();

            Log::info('Faculty successfully updated', [
                'faculty_id' => $facultyProfile->id,
                'schedules_updated' => $scheduleUpdateCount
            ]);

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty member updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Faculty update failed', [
                'faculty_id' => $facultyProfile->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);

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
            DB::beginTransaction();

            if ($faculty->faculty) {
                $facultyId = $faculty->faculty->id;

                // Delete related educational backgrounds
                EducationalBackground::where('faculty_id', $facultyId)->delete();
                
                // Delete related subjects
                FacultySubject::where('faculty_id', $facultyId)->delete();
                
                // Delete unavailabilities
                FacultyUnavailability::where('faculty_id', $facultyId)->delete();
                
                // Delete or handle schedules (depending on your business logic)
                // Option 1: Delete schedules
                Schedule::where('faculty_id', $faculty->id)->delete();
                
                // Option 2: Set faculty_id to null (if you allow orphaned schedules)
                // Schedule::where('faculty_id', $faculty->id)->update(['faculty_id' => null, 'faculty_code' => null]);
                
                // Delete faculty profile
                $faculty->faculty->delete();

                Log::info('Faculty profile and related data deleted', ['faculty_id' => $facultyId]);
            }

            // Delete user
            $faculty->delete();

            Log::info('Faculty user deleted', ['user_id' => $faculty->id]);

            DB::commit();

            return redirect()
                ->route('admin.faculty.index')
                ->with('success', 'Faculty member deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Faculty deletion failed', [
                'user_id' => $faculty->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

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
        try {
            $user = User::findOrFail($id);
            $facultyProfile = $user->faculty;

            if (!$facultyProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty profile not found'
                ], 404);
            }

            $subjects = Subject::orderBy('subject_name')->get();
            $assignedSubjects = $facultyProfile->facultySubjects()->pluck('subject_id')->toArray();

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'assignedSubjects' => $assignedSubjects
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch subjects', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects'
            ], 500);
        }
    }

    /**
     * Get assigned subjects with details
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
                    'class_size' => $fs->class_size,
                    'faculty_code' => $fs->faculty_code,
                ]);

            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch assigned subjects', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assigned subjects'
            ], 500);
        }
    }

    /**
     * Assign subjects (simplified)
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

            $request->validate([
                'subjects' => 'nullable|array',
                'subjects.*' => 'exists:subjects,id'
            ]);

            DB::beginTransaction();

            // Delete old assignments
            FacultySubject::where('faculty_id', $facultyProfile->id)->delete();

            // Add new ones
            $assignedCount = 0;
            if ($request->filled('subjects')) {
                foreach ($request->subjects as $subjectId) {
                    // Get subject to use default units
                    $subject = Subject::find($subjectId);
                    
                    if ($subject) {
                        FacultySubject::create([
                            'faculty_id' => $facultyProfile->id,
                            'faculty_code' => $facultyProfile->faculty_code,
                            'subject_id' => $subjectId,
                            'program_id' => $subject->program_id ?? null,
                            'lecture_units' => $subject->lec ?? 0,
                            'laboratory_units' => $subject->lab ?? 0,
                            'year_level' => $subject->year_level ?? null,
                            'semester' => $subject->semester ?? null,
                            'class_size' => 0, // Default class size
                        ]);
                        $assignedCount++;
                    }
                }
            }

            DB::commit();

            Log::info('Subjects assigned via modal', [
                'faculty_id' => $facultyProfile->id,
                'faculty_code' => $facultyProfile->faculty_code,
                'count' => $assignedCount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subjects assigned successfully.',
                'count' => $assignedCount
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to assign subjects', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign subjects: ' . $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\FacultySubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculty members.
     */
    public function index()
    {
        $faculties = User::where('role', 'faculty')
            ->withCount([
                'enrollments',
                'enrollments as pending_count' => function ($query) {
                    $query->where('enrollment_status', 'pending');
                },
                'enrollments as active_count' => function ($query) {
                    $query->where('enrollment_status', 'accepted');
                }
            ])
            ->with(['enrollments' => function ($query) {
                $query->where('enrollment_status', 'pending');
            }])
            ->get();

        return view('admin.faculty.index', compact('faculties'));
    }

    /**
     * Get subjects for a specific faculty (AJAX endpoint).
     */
    public function getSubjects($userId)
    {
        try {
            // Find the user with role relationship
            $faculty = User::with('role')->findOrFail($userId);
            
            \Log::info('Fetching subjects for user:', [
                'user_id' => $faculty->id,
                'user_role' => $faculty->role,
                'user_name' => $faculty->name
            ]);
            
            // Check role - handle both relationship and direct role column
            $roleName = null;
            
            if (is_object($faculty->role) && isset($faculty->role->name)) {
                // Role is a relationship
                $roleName = strtolower($faculty->role->name);
            } elseif (is_string($faculty->role)) {
                // Role is a string column
                $roleName = strtolower($faculty->role);
            } elseif (isset($faculty->role_id)) {
                // Try to get role by role_id
                $role = \DB::table('roles')->where('id', $faculty->role_id)->first();
                if ($role) {
                    $roleName = strtolower($role->name);
                }
            }
            
            \Log::info('Resolved role name:', ['role_name' => $roleName]);
            
            if ($roleName !== 'faculty') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid faculty member. User role is: ' . ($roleName ?? 'unknown')
                ], 400);
            }

            // Get all subjects
            $subjects = Subject::orderBy('year_level')
                ->orderBy('semester')
                ->orderBy('subject_name')
                ->get()
                ->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'course_code' => $subject->course_code,
                        'subject_name' => $subject->subject_name,
                        'units' => $subject->units,
                        'semester' => $subject->semester,
                        'year_level' => $subject->year_level,
                        'enrolled_student' => $subject->enrolled_student,
                    ];
                });

            \Log::info('Found subjects:', ['count' => $subjects->count()]);

            // Get already assigned subjects for this faculty
            $assignedSubjects = DB::table('faculty_subjects')
                ->where('faculty_id', $faculty->id)
                ->pluck('subject_id')
                ->toArray();

            \Log::info('Assigned subjects:', ['count' => count($assignedSubjects)]);

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'assignedSubjects' => $assignedSubjects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching subjects: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign subjects to a faculty member.
     */
    public function assignSubjects(Request $request, $userId)
    {
        try {
            // Find the user with role relationship
            $faculty = User::with('role')->findOrFail($userId);
            
            // Check role - handle both relationship and direct role column
            $roleName = null;
            
            if (is_object($faculty->role) && isset($faculty->role->name)) {
                $roleName = strtolower($faculty->role->name);
            } elseif (is_string($faculty->role)) {
                $roleName = strtolower($faculty->role);
            } elseif (isset($faculty->role_id)) {
                $role = \DB::table('roles')->where('id', $faculty->role_id)->first();
                if ($role) {
                    $roleName = strtolower($role->name);
                }
            }
            
            if ($roleName !== 'faculty') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid faculty member'
                ], 400);
            }

            $request->validate([
                'subjects' => 'nullable|array',
                'subjects.*' => 'exists:subjects,id'
            ]);

            DB::beginTransaction();

            // Remove all existing assignments
            DB::table('faculty_subjects')
                ->where('faculty_id', $faculty->id)
                ->delete();

            // Add new assignments if any subjects were selected
            if ($request->has('subjects') && !empty($request->subjects)) {
                $assignments = collect($request->subjects)->map(function ($subjectId) use ($faculty) {
                    return [
                        'faculty_id' => $faculty->id,
                        'subject_id' => $subjectId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                })->toArray();

                DB::table('faculty_subjects')->insert($assignments);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subjects assigned successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign subjects: ' . $e->getMessage()
            ], 500);
        }
    }
}
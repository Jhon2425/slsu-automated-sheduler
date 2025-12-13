<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\Program;
use App\Services\ScheduleGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $scheduleGenerator;

    public function __construct(ScheduleGeneratorService $scheduleGenerator)
    {
        $this->scheduleGenerator = $scheduleGenerator;
    }

    /**
     * Display schedule management page
     */
    public function index()
    {
        // Get schedules with relationships
        $schedules = Schedule::with(['faculty', 'subject', 'room', 'program'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Get all programs for the generation form
        // Remove the status check if users table doesn't have status column
        $programs = Program::with(['facultySubjects' => function ($query) {
            $query->with(['faculty', 'subject']);
            // Only include if you have a status column in users table
            // ->whereHas('faculty', function ($q) {
            //     $q->where('status', 'accepted');
            // });
        }])->get();

        return view('admin.schedules.index', compact('schedules', 'programs'));
    }

    /**
     * Generate schedule for a program
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'type' => 'nullable|in:regular,make_up,special'
        ]);

        try {
            $result = $this->scheduleGenerator->generateSchedule(
                $validated['program_id'],
                $validated['semester'],
                $validated['academic_year'],
                $validated['type'] ?? 'regular'
            );

            if (count($result['conflicts']) > 0) {
                return redirect()->route('admin.schedules.index')
                    ->with('warning', 'Schedule generated with ' . count($result['conflicts']) . ' conflicts. Please review.')
                    ->with('generated_schedules', $result['schedules'])
                    ->with('conflicts', $result['conflicts']);
            }

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule generated successfully with ' . count($result['schedules']) . ' entries.')
                ->with('generated_schedules', $result['schedules']);

        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Failed to generate schedule: ' . $e->getMessage());
        }
    }

    /**
     * Save generated schedule to database
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.faculty_id' => 'required|exists:users,id',
            'schedules.*.subject_id' => 'required|exists:subjects,id',
            'schedules.*.room_id' => 'required|exists:rooms,id',
            'schedules.*.program_id' => 'required|exists:programs,id',
            'schedules.*.day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i',
            'schedules.*.class_type' => 'required|in:lecture,laboratory',
            'schedules.*.semester' => 'required|string',
            'schedules.*.academic_year' => 'required|string',
            'schedules.*.type' => 'required|in:regular,make_up,special',
        ]);

        try {
            $saved = $this->scheduleGenerator->saveSchedule($validated['schedules']);

            if ($saved) {
                return response()->json([
                    'success' => true,
                    'message' => 'Schedule saved successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to save schedule'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get faculty schedule with relationships
     */
    public function getFacultySchedule(Request $request, int $facultyId)
    {
        $validated = $request->validate([
            'semester' => 'required|string',
            'academic_year' => 'required|string'
        ]);

        try {
            $schedule = Schedule::where('faculty_id', $facultyId)
                ->where('semester', $validated['semester'])
                ->where('academic_year', $validated['academic_year'])
                ->with(['subject', 'room', 'program'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            $faculty = User::findOrFail($facultyId);

            return response()->json([
                'success' => true,
                'faculty' => $faculty,
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get room schedule with relationships
     */
    public function getRoomSchedule(Request $request, int $roomId)
    {
        $validated = $request->validate([
            'semester' => 'required|string',
            'academic_year' => 'required|string'
        ]);

        try {
            $schedule = Schedule::where('room_id', $roomId)
                ->where('semester', $validated['semester'])
                ->where('academic_year', $validated['academic_year'])
                ->with(['faculty', 'subject', 'program'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            $room = Room::findOrFail($roomId);

            return response()->json([
                'success' => true,
                'room' => $room,
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get program schedule with relationships
     */
    public function getProgramSchedule(Request $request, int $programId)
    {
        $validated = $request->validate([
            'semester' => 'required|string',
            'academic_year' => 'required|string'
        ]);

        try {
            $schedule = Schedule::where('program_id', $programId)
                ->where('semester', $validated['semester'])
                ->where('academic_year', $validated['academic_year'])
                ->with(['faculty', 'subject', 'room'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            $program = Program::with(['facultySubjects.faculty', 'facultySubjects.subject'])
                ->findOrFail($programId);

            return response()->json([
                'success' => true,
                'program' => $program,
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all programs for schedule generation
     */
    public function getPrograms()
    {
        try {
            $programs = Program::with(['facultySubjects' => function ($query) {
                $query->with(['faculty', 'subject']);
                // Only include if you have a status column in users table
                // ->whereHas('faculty', function ($q) {
                //     $q->where('status', 'accepted');
                // });
            }])->get();

            return response()->json([
                'success' => true,
                'programs' => $programs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for schedule conflicts
     */
    public function checkConflicts(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'nullable|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'exclude_schedule_id' => 'nullable|exists:schedules,id'
        ]);

        try {
            $conflicts = [];

            // Check faculty conflicts
            if (isset($validated['faculty_id'])) {
                $facultyConflicts = Schedule::where('faculty_id', $validated['faculty_id'])
                    ->where('day', $validated['day'])
                    ->where('semester', $validated['semester'])
                    ->where('academic_year', $validated['academic_year'])
                    ->where(function ($query) use ($validated) {
                        $query->where(function ($q) use ($validated) {
                            $q->where('start_time', '<', $validated['end_time'])
                              ->where('end_time', '>', $validated['start_time']);
                        });
                    })
                    ->when(isset($validated['exclude_schedule_id']), function ($q) use ($validated) {
                        $q->where('id', '!=', $validated['exclude_schedule_id']);
                    })
                    ->with(['subject', 'room'])
                    ->get();

                if ($facultyConflicts->isNotEmpty()) {
                    $conflicts['faculty'] = $facultyConflicts;
                }
            }

            // Check room conflicts
            if (isset($validated['room_id'])) {
                $roomConflicts = Schedule::where('room_id', $validated['room_id'])
                    ->where('day', $validated['day'])
                    ->where('semester', $validated['semester'])
                    ->where('academic_year', $validated['academic_year'])
                    ->where(function ($query) use ($validated) {
                        $query->where(function ($q) use ($validated) {
                            $q->where('start_time', '<', $validated['end_time'])
                              ->where('end_time', '>', $validated['start_time']);
                        });
                    })
                    ->when(isset($validated['exclude_schedule_id']), function ($q) use ($validated) {
                        $q->where('id', '!=', $validated['exclude_schedule_id']);
                    })
                    ->with(['faculty', 'subject'])
                    ->get();

                if ($roomConflicts->isNotEmpty()) {
                    $conflicts['room'] = $roomConflicts;
                }
            }

            return response()->json([
                'success' => true,
                'has_conflicts' => !empty($conflicts),
                'conflicts' => $conflicts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download schedule as PDF
     */
    public function downloadPDF(Request $request)
    {
        $query = Schedule::with(['faculty', 'subject', 'room', 'program'])
            ->orderBy('day')
            ->orderBy('start_time');

        // Apply filters if provided
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->has('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        $schedules = $query->get();

        $pdf = Pdf::loadView('admin.schedules.pdf', compact('schedules'));
        
        $filename = 'schedule-' . date('Y-m-d') . '.pdf';
        if ($request->has('program_id')) {
            $program = Program::find($request->program_id);
            $filename = 'schedule-' . $program->code . '-' . date('Y-m-d') . '.pdf';
        }

        return $pdf->download($filename);
    }
}
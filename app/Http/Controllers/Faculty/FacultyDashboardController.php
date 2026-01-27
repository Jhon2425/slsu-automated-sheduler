<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\FacultyEnrollment;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacultyDashboardController extends Controller
{
    public function index()
    {
        $facultyId = Auth::id();
        $user = Auth::user();
        $faculty = Auth::user();

        // Get IDs of programs the faculty is already enrolled in
        $enrolledProgramIds = FacultyEnrollment::where('faculty_id', $facultyId)
            ->pluck('program_id')
            ->toArray();

        // Get available programs (not enrolled)
        $availablePrograms = Program::whereNotIn('id', $enrolledProgramIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get programs faculty is enrolled in with schedules
        $enrolledPrograms = FacultyEnrollment::where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get assigned subjects for this faculty member
        $assignedSubjects = $user->subjects()->with('program')->get();

        // Get schedules for this faculty member
        $schedules = Schedule::where('faculty_id', $facultyId)
            ->with(['subject', 'program', 'classroom'])
            ->orderBy('start_time')
            ->get();

        // Academic Year and Semester (you can adjust this logic based on your needs)
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Determine academic year (e.g., if month >= 6, it's the start of new academic year)
        if ($currentMonth >= 6) {
            $academicYear = $currentYear . '-' . ($currentYear + 1);
            $schoolYear = $currentYear . '-' . ($currentYear + 1);
        } else {
            $academicYear = ($currentYear - 1) . '-' . $currentYear;
            $schoolYear = ($currentYear - 1) . '-' . $currentYear;
        }
        
        // Determine semester (adjust months as needed for your institution)
        if ($currentMonth >= 6 && $currentMonth <= 10) {
            $semester = '1st';
        } elseif ($currentMonth >= 11 || $currentMonth <= 3) {
            $semester = '2nd';
        } else {
            $semester = 'Summer';
        }

        // Educational Qualifications (fetch from database if you have a table for this)
        $educationalQualifications = [];
        
        // Check if faculty has educational_qualifications field (JSON)
        if ($faculty->educational_qualifications) {
            $educationalQualifications = json_decode($faculty->educational_qualifications, true);
        }

        // Calculate total contact hours and units
        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            // Calculate contact hours
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
            
            // Calculate units
            $units = ($schedule->lecture_units ?? 0) + ($schedule->laboratory_units ?? 0);
            $totalUnits += $units;
        }

        // Calculate excess load (assuming normal load is 18 units for full-time)
        $normalLoad = $faculty->employment_status === 'FULL-TIME' ? 18 : 12;
        $excessLoad = $totalUnits > $normalLoad ? ($totalUnits - $normalLoad) . ' units' : 'NONE';

        // Total workload per day calculation
        $workloadPerDay = $schedules->groupBy(function($schedule) {
            return $schedule->day_name;
        })->map(function($daySchedules) {
            $dailyHours = 0;
            foreach ($daySchedules as $schedule) {
                $start = strtotime($schedule->start_time);
                $end = strtotime($schedule->end_time);
                $dailyHours += ($end - $start) / 3600;
            }
            return $dailyHours;
        });
        
        $totalWorkloadPerDay = $workloadPerDay->avg() ?? 0;
        $totalWorkloadPerDay = round($totalWorkloadPerDay, 2) . ' hours/day';

        // Other officials (you can store these in settings table or env)
        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident = config('app.vice_president', 'GONDELINA A. MADOVAN, PhD');
        
        // Date effective (you can adjust based on your needs)
        $dateEffective = date('F d, Y', strtotime($faculty->created_at ?? now()));

        return view('faculty.dashboard', compact(
            'availablePrograms',
            'enrolledPrograms',
            'assignedSubjects',
            'faculty',
            'schedules',
            'academicYear',
            'schoolYear',
            'semester',
            'educationalQualifications',
            'totalContactHours',
            'totalUnits',
            'excessLoad',
            'totalWorkloadPerDay',
            'campusDirector',
            'vicePresident',
            'dateEffective'
        ));
    }

    /**
     * Enroll faculty to a program
     */
    public function enrollProgram($programId)
    {
        $facultyId = Auth::id();

        // Check if already enrolled
        $exists = FacultyEnrollment::where('faculty_id', $facultyId)
            ->where('program_id', $programId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'You are already enrolled in this program.');
        }

        FacultyEnrollment::create([
            'faculty_id' => $facultyId,
            'program_id' => $programId,
            'enrollment_status' => 'pending', // Admin needs to approve
        ]);

        return redirect()->back()->with('success', 'Successfully enrolled! Waiting for admin approval.');
    }

    /**
     * Unenroll from a program
     */
    public function unenrollProgram($enrollmentId)
    {
        $facultyId = Auth::id();

        $enrollment = FacultyEnrollment::where('id', $enrollmentId)
            ->where('faculty_id', $facultyId)
            ->firstOrFail();

        $enrollment->delete();

        return redirect()->back()->with('success', 'Successfully unenrolled from the program.');
    }

    /**
     * View schedule - Shows the faculty's complete teaching load document
     */
    public function viewSchedule()
    {
        $facultyId = Auth::id();
        $faculty = Auth::user();

        // Get schedules for this faculty member
        $schedules = Schedule::where('faculty_id', $facultyId)
            ->with(['subject', 'program', 'classroom'])
            ->orderBy('start_time')
            ->get();

        // Get enrolled programs
        $enrolledPrograms = FacultyEnrollment::where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get assigned subjects
        $assignedSubjects = $faculty->subjects()->with('program')->get();

        // Academic year and semester
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        if ($currentMonth >= 6) {
            $academicYear = $currentYear . '-' . ($currentYear + 1);
            $schoolYear = $currentYear . '-' . ($currentYear + 1);
        } else {
            $academicYear = ($currentYear - 1) . '-' . $currentYear;
            $schoolYear = ($currentYear - 1) . '-' . $currentYear;
        }
        
        if ($currentMonth >= 6 && $currentMonth <= 10) {
            $semester = '1st';
        } elseif ($currentMonth >= 11 || $currentMonth <= 3) {
            $semester = '2nd';
        } else {
            $semester = 'Summer';
        }

        // Educational qualifications
        $educationalQualifications = [];
        if ($faculty->educational_qualifications) {
            $educationalQualifications = json_decode($faculty->educational_qualifications, true);
        }

        // Calculate totals
        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
            
            $units = ($schedule->lecture_units ?? 0) + ($schedule->laboratory_units ?? 0);
            $totalUnits += $units;
        }

        $normalLoad = $faculty->employment_status === 'FULL-TIME' ? 18 : 12;
        $excessLoad = $totalUnits > $normalLoad ? ($totalUnits - $normalLoad) . ' units' : 'NONE';

        $workloadPerDay = $schedules->groupBy(function($schedule) {
            return $schedule->day_name;
        })->map(function($daySchedules) {
            $dailyHours = 0;
            foreach ($daySchedules as $schedule) {
                $start = strtotime($schedule->start_time);
                $end = strtotime($schedule->end_time);
                $dailyHours += ($end - $start) / 3600;
            }
            return $dailyHours;
        });
        
        $totalWorkloadPerDay = round($workloadPerDay->avg() ?? 0, 2) . ' hours/day';

        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident = config('app.vice_president', 'GONDELINA A. MADOVAN, PhD');
        $dateEffective = date('F d, Y', strtotime($faculty->created_at ?? now()));

        // Return the teaching load view (the document you showed me earlier)
        return view('faculty.teaching-load', compact(
            'faculty',
            'schedules',
            'academicYear',
            'schoolYear',
            'semester',
            'educationalQualifications',
            'totalContactHours',
            'totalUnits',
            'excessLoad',
            'totalWorkloadPerDay',
            'campusDirector',
            'vicePresident',
            'dateEffective',
            'assignedSubjects',
            'enrolledPrograms'
        ));
    }

    /**
     * Download schedule for a specific enrollment
     */
    public function downloadSchedule($enrollmentId)
    {
        $facultyId = Auth::id();

        $enrollment = FacultyEnrollment::where('id', $enrollmentId)
            ->where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->firstOrFail();

        // Generate PDF logic here
        $pdf = \PDF::loadView('faculty.schedule-pdf', compact('enrollment'));
        
        return $pdf->download('schedule-' . $enrollment->program->code . '.pdf');
    }

    /**
     * Download all schedules (legacy)
     */
    public function downloadPDF()
    {
        $facultyId = Auth::id();
        $faculty = Auth::user();
        
        // Get all the same data as viewSchedule method
        $schedules = Schedule::where('faculty_id', $facultyId)
            ->with(['subject', 'program', 'classroom'])
            ->orderBy('start_time')
            ->get();

        $enrolledPrograms = FacultyEnrollment::where('faculty_id', $facultyId)
            ->with(['program', 'schedules'])
            ->get();

        $assignedSubjects = $faculty->subjects()->with('program')->get();

        $currentYear = date('Y');
        $currentMonth = date('n');
        
        if ($currentMonth >= 6) {
            $academicYear = $currentYear . '-' . ($currentYear + 1);
            $schoolYear = $currentYear . '-' . ($currentYear + 1);
        } else {
            $academicYear = ($currentYear - 1) . '-' . $currentYear;
            $schoolYear = ($currentYear - 1) . '-' . $currentYear;
        }
        
        if ($currentMonth >= 6 && $currentMonth <= 10) {
            $semester = '1st';
        } elseif ($currentMonth >= 11 || $currentMonth <= 3) {
            $semester = '2nd';
        } else {
            $semester = 'Summer';
        }

        $educationalQualifications = [];
        if ($faculty->educational_qualifications) {
            $educationalQualifications = json_decode($faculty->educational_qualifications, true);
        }

        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
            
            $units = ($schedule->lecture_units ?? 0) + ($schedule->laboratory_units ?? 0);
            $totalUnits += $units;
        }

        $normalLoad = $faculty->employment_status === 'FULL-TIME' ? 18 : 12;
        $excessLoad = $totalUnits > $normalLoad ? ($totalUnits - $normalLoad) . ' units' : 'NONE';

        $workloadPerDay = $schedules->groupBy(function($schedule) {
            return $schedule->day_name;
        })->map(function($daySchedules) {
            $dailyHours = 0;
            foreach ($daySchedules as $schedule) {
                $start = strtotime($schedule->start_time);
                $end = strtotime($schedule->end_time);
                $dailyHours += ($end - $start) / 3600;
            }
            return $dailyHours;
        });
        
        $totalWorkloadPerDay = round($workloadPerDay->avg() ?? 0, 2) . ' hours/day';

        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident = config('app.vice_president', 'GONDELINA A. MADOVAN, PhD');
        $dateEffective = date('F d, Y', strtotime($faculty->created_at ?? now()));

        $pdf = \PDF::loadView('faculty.teaching-load-pdf', compact(
            'enrolledPrograms',
            'faculty',
            'assignedSubjects',
            'schedules',
            'academicYear',
            'schoolYear',
            'semester',
            'educationalQualifications',
            'totalContactHours',
            'totalUnits',
            'excessLoad',
            'totalWorkloadPerDay',
            'campusDirector',
            'vicePresident',
            'dateEffective'
        ));
        
        return $pdf->download('teaching-load-' . $faculty->name . '.pdf');
    }
}
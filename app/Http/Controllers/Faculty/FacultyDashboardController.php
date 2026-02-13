<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\FacultySubject;
use App\Models\EducationalBackground;
use App\Models\Faculty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacultyDashboardController extends Controller
{
    public function index()
    {
        $facultyId = Auth::id();
        $user = Auth::user();
        
        // Get faculty details from faculty table using faculty_code
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->first();
        
        // If no faculty record exists, fall back to user data
        if (!$faculty) {
            $faculty = $user;
        }
        
        // Get the faculty table's ID to use in queries
        $facultyTableId = $faculty->id ?? $facultyId;

        // Get assigned subjects for this faculty member with their programs
        $assignedSubjects = FacultySubject::where('faculty_id', $facultyTableId)
            ->with(['subject.program', 'subject'])
            ->get();

        // Get schedules for this faculty member
        $schedules = Schedule::where('faculty_id', $facultyTableId)
            ->with(['subject.program', 'subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Get educational background
        $educationalQualifications = EducationalBackground::where('faculty_id', $facultyTableId)
            ->orderBy('year_graduated', 'desc')
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

        // Calculate total contact hours and units
        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            // Calculate contact hours from schedule times
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
        }
        
        // Calculate total units from assigned subjects
        foreach ($assignedSubjects as $assignment) {
            // Get units from faculty_subject pivot table (if available)
            if (isset($assignment->lecture_units) && isset($assignment->laboratory_units)) {
                $units = $assignment->lecture_units + $assignment->laboratory_units;
            }
            // Otherwise get from subject table
            elseif ($assignment->subject) {
                $units = ($assignment->subject->lecture_units ?? 0) + ($assignment->subject->laboratory_units ?? 0);
            } else {
                $units = 0;
            }
            $totalUnits += $units;
        }

        // Calculate excess load (assuming normal load is 18 units for full-time, 12 for part-time)
        $normalLoad = $faculty->employment_status === 'FULL-TIME' ? 18 : 12;
        $excessLoad = $totalUnits > $normalLoad ? ($totalUnits - $normalLoad) . ' units' : 'NONE';

        // Total workload per day calculation
        $workloadPerDay = $schedules->groupBy(function($schedule) {
            return $schedule->day;
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
     * View schedule - Shows the faculty's complete teaching load document
     */
    public function viewSchedule()
    {
        $facultyId = Auth::id();
        $user = Auth::user();
        
        // Get faculty details from faculty table using faculty_code
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->first();
        
        // If no faculty record exists, fall back to user data
        if (!$faculty) {
            $faculty = $user;
        }
        
        // Get the faculty table's ID to use in queries
        $facultyTableId = $faculty->id ?? $facultyId;

        // Get schedules for this faculty member
        $schedules = Schedule::where('faculty_id', $facultyTableId)
            ->with(['subject.program', 'subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Get assigned subjects
        $assignedSubjects = FacultySubject::where('faculty_id', $facultyTableId)
            ->with(['subject.program', 'subject'])
            ->get();

        // Get educational background
        $educationalQualifications = EducationalBackground::where('faculty_id', $facultyTableId)
            ->orderBy('year_graduated', 'desc')
            ->get();

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

        // Calculate totals
        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            // Calculate contact hours from schedule times
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
        }
        
        // Calculate total units from assigned subjects
        foreach ($assignedSubjects as $assignment) {
            // Get units from faculty_subject pivot table (if available)
            if (isset($assignment->lecture_units) && isset($assignment->laboratory_units)) {
                $units = $assignment->lecture_units + $assignment->laboratory_units;
            }
            // Otherwise get from subject table
            elseif ($assignment->subject) {
                $units = ($assignment->subject->lecture_units ?? 0) + ($assignment->subject->laboratory_units ?? 0);
            } else {
                $units = 0;
            }
            $totalUnits += $units;
        }

        $normalLoad = $faculty->employment_status === 'FULL-TIME' ? 18 : 12;
        $excessLoad = $totalUnits > $normalLoad ? ($totalUnits - $normalLoad) . ' units' : 'NONE';

        $workloadPerDay = $schedules->groupBy(function($schedule) {
            return $schedule->day;
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

        // Return the teaching load view
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
            'assignedSubjects'
        ));
    }

    /**
     * Download teaching load as PDF
     */
    public function downloadPDF()
    {
        $facultyId = Auth::id();
        $user = Auth::user();
        
        // Get faculty details from faculty table using faculty_code
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->first();
        
        // If no faculty record exists, fall back to user data
        if (!$faculty) {
            $faculty = $user;
        }
        
        // Get the faculty table's ID to use in queries
        $facultyTableId = $faculty->id ?? $facultyId;
        
        // Get all the same data as viewSchedule method
        $schedules = Schedule::where('faculty_id', $facultyTableId)
            ->with(['subject.program', 'subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $assignedSubjects = FacultySubject::where('faculty_id', $facultyTableId)
            ->with(['subject.program', 'subject'])
            ->get();

        $educationalQualifications = EducationalBackground::where('faculty_id', $facultyTableId)
            ->orderBy('year_graduated', 'desc')
            ->get();

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

        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            // Calculate contact hours from schedule times
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
        }
        
        // Calculate total units from assigned subjects
        foreach ($assignedSubjects as $assignment) {
            // Get units from faculty_subject pivot table (if available)
            if (isset($assignment->lecture_units) && isset($assignment->laboratory_units)) {
                $units = $assignment->lecture_units + $assignment->laboratory_units;
            }
            // Otherwise get from subject table
            elseif ($assignment->subject) {
                $units = ($assignment->subject->lecture_units ?? 0) + ($assignment->subject->laboratory_units ?? 0);
            } else {
                $units = 0;
            }
            $totalUnits += $units;
        }

        $normalLoad = $faculty->employment_status === 'FULL-TIME' ? 18 : 12;
        $excessLoad = $totalUnits > $normalLoad ? ($totalUnits - $normalLoad) . ' units' : 'NONE';

        $workloadPerDay = $schedules->groupBy(function($schedule) {
            return $schedule->day;
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
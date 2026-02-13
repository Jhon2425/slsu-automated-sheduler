<?php

// File: app/Http/Controllers/Faculty/TeachingLoadController.php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\EducationalBackground;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TeachingLoadController extends Controller
{
    public function index(Request $request)
    {
        $facultyId = Auth::id();
        $user = Auth::user();
        
        // Get faculty details from faculty table using faculty_code
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->first();
        
        // If no faculty record exists, fall back to user data
        if (!$faculty) {
            $faculty = $user;
        }
        
        // Get current academic period or from request (using simple date logic)
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Determine academic year
        if ($currentMonth >= 6) {
            $defaultSchoolYear = $currentYear . '-' . ($currentYear + 1);
        } else {
            $defaultSchoolYear = ($currentYear - 1) . '-' . $currentYear;
        }
        
        // Determine semester
        if ($currentMonth >= 6 && $currentMonth <= 10) {
            $defaultSemester = '1st';
        } elseif ($currentMonth >= 11 || $currentMonth <= 3) {
            $defaultSemester = '2nd';
        } else {
            $defaultSemester = 'Summer';
        }
        
        $schoolYear = $request->get('school_year', $defaultSchoolYear);
        $semester = $request->get('semester', $defaultSemester);
        
        // Get the faculty table's ID (not the user id) to use in queries
        $facultyTableId = $faculty->id ?? $facultyId;
        
        // Get faculty's schedules with relationships
        $schedules = Schedule::where('faculty_id', $facultyTableId)
            ->where('is_active', true)
            ->where('academic_year', $schoolYear)
            ->where('semester', $semester)
            ->with(['subject.program', 'subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
        
        // Get educational qualifications from educational_background table
        $educationalQualifications = EducationalBackground::where('faculty_id', $facultyTableId)
            ->orderBy('year_graduated', 'desc')
            ->get();
        
        // Create empty administrative assignments array (functionality not yet implemented)
        $assignmentsByType = [
            'Designation' => null,
            'Committee Work' => null,
            'Research Work' => null,
            'Extension' => null,
            'Production' => null,
        ];
        
        // Calculate totals
        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            // Calculate contact hours
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
            
            // Calculate units - check both schedule and subject for units
            if (isset($schedule->lecture_units) && isset($schedule->laboratory_units)) {
                $units = $schedule->lecture_units + $schedule->laboratory_units;
            } elseif ($schedule->subject) {
                $units = ($schedule->subject->lecture_units ?? 0) + ($schedule->subject->laboratory_units ?? 0);
            } else {
                $units = 0;
            }
            $totalUnits += $units;
        }
        
        // Calculate excess load (assuming standard load is 21 units)
        $standardLoad = 21;
        $excessLoad = $totalUnits > $standardLoad ? ($totalUnits - $standardLoad) : 0;
        $excessLoadDisplay = $excessLoad > 0 ? number_format($excessLoad, 1) . ' units' : 'NONE';
        
        // Get unique subjects for number of preparations
        $numberOfPreparations = $schedules->unique('subject_id')->count();
        
        // Calculate total workload per day
        $workloadPerDay = [];
        foreach ($schedules->groupBy('day') as $day => $daySchedules) {
            $dailyHours = $daySchedules->sum(function ($schedule) {
                $start = strtotime($schedule->start_time);
                $end = strtotime($schedule->end_time);
                return ($end - $start) / 3600;
            });
            $workloadPerDay[$day] = $dailyHours;
        }
        $totalWorkloadPerDay = !empty($workloadPerDay) 
            ? number_format(array_sum($workloadPerDay) / count($workloadPerDay), 2) . ' hours' 
            : 'Not set';
        
        // Get system settings for officials (use config or default values)
        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident = config('app.vice_president', 'GONDELINA A. MADOVAN, PhD');
        $dateEffective = config('app.default_date_effective', 'August 30, 2023');
        
        // Format date effective
        if ($dateEffective) {
            try {
                $dateEffective = date('F d, Y', strtotime($dateEffective));
            } catch (\Exception $e) {
                $dateEffective = 'August 30, 2023';
            }
        } else {
            $dateEffective = 'August 30, 2023';
        }
        
        // Format appointment date
        if ($faculty->appointment_date) {
            $faculty->formatted_appointment_date = date('F Y', strtotime($faculty->appointment_date));
        }
        
        return view('faculty.teaching-load', compact(
            'faculty',
            'schedules',
            'educationalQualifications',
            'assignmentsByType',
            'totalContactHours',
            'totalUnits',
            'excessLoadDisplay',
            'numberOfPreparations',
            'totalWorkloadPerDay',
            'schoolYear',
            'semester',
            'campusDirector',
            'vicePresident',
            'dateEffective'
        ));
    }
    
    public function downloadPdf(Request $request)
    {
        $facultyId = Auth::id();
        $user = Auth::user();
        
        // Get faculty details from faculty table using faculty_code
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->first();
        
        // If no faculty record exists, fall back to user data
        if (!$faculty) {
            $faculty = $user;
        }
        
        // Get current academic period or from request (using simple date logic)
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Determine academic year
        if ($currentMonth >= 6) {
            $defaultSchoolYear = $currentYear . '-' . ($currentYear + 1);
        } else {
            $defaultSchoolYear = ($currentYear - 1) . '-' . $currentYear;
        }
        
        // Determine semester
        if ($currentMonth >= 6 && $currentMonth <= 10) {
            $defaultSemester = '1st';
        } elseif ($currentMonth >= 11 || $currentMonth <= 3) {
            $defaultSemester = '2nd';
        } else {
            $defaultSemester = 'Summer';
        }
        
        $schoolYear = $request->get('school_year', $defaultSchoolYear);
        $semester = $request->get('semester', $defaultSemester);
        
        // Get the faculty table's ID (not the user id) to use in queries
        $facultyTableId = $faculty->id ?? $facultyId;
        
        // Get all the same data as index method
        $schedules = Schedule::where('faculty_id', $facultyTableId)
            ->where('is_active', true)
            ->where('academic_year', $schoolYear)
            ->where('semester', $semester)
            ->with(['subject.program', 'subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
        
        // Get educational qualifications from educational_background table
        $educationalQualifications = EducationalBackground::where('faculty_id', $facultyTableId)
            ->orderBy('year_graduated', 'desc')
            ->get();
        
        // Create empty administrative assignments array (functionality not yet implemented)
        $assignmentsByType = [
            'Designation' => null,
            'Committee Work' => null,
            'Research Work' => null,
            'Extension' => null,
            'Production' => null,
        ];
        
        // Calculate totals (same as index)
        $totalContactHours = 0;
        $totalUnits = 0;
        
        foreach ($schedules as $schedule) {
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
            
            // Calculate units - check both schedule and subject for units
            if (isset($schedule->lecture_units) && isset($schedule->laboratory_units)) {
                $units = $schedule->lecture_units + $schedule->laboratory_units;
            } elseif ($schedule->subject) {
                $units = ($schedule->subject->lecture_units ?? 0) + ($schedule->subject->laboratory_units ?? 0);
            } else {
                $units = 0;
            }
            $totalUnits += $units;
        }
        
        $standardLoad = 21;
        $excessLoad = $totalUnits > $standardLoad ? ($totalUnits - $standardLoad) : 0;
        $excessLoadDisplay = $excessLoad > 0 ? number_format($excessLoad, 1) . ' units' : 'NONE';
        
        $numberOfPreparations = $schedules->unique('subject_id')->count();
        
        $workloadPerDay = [];
        foreach ($schedules->groupBy('day') as $day => $daySchedules) {
            $dailyHours = $daySchedules->sum(function ($schedule) {
                $start = strtotime($schedule->start_time);
                $end = strtotime($schedule->end_time);
                return ($end - $start) / 3600;
            });
            $workloadPerDay[$day] = $dailyHours;
        }
        $totalWorkloadPerDay = !empty($workloadPerDay) 
            ? number_format(array_sum($workloadPerDay) / count($workloadPerDay), 2) . ' hours' 
            : 'Not set';
        
        // Get system settings for officials (use config or default values)
        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident = config('app.vice_president', 'GONDELINA A. MADOVAN, PhD');
        $dateEffective = config('app.default_date_effective', 'August 30, 2023');
        
        if ($dateEffective) {
            try {
                $dateEffective = date('F d, Y', strtotime($dateEffective));
            } catch (\Exception $e) {
                $dateEffective = 'August 30, 2023';
            }
        } else {
            $dateEffective = 'August 30, 2023';
        }
        
        if ($faculty->appointment_date) {
            $faculty->formatted_appointment_date = date('F Y', strtotime($faculty->appointment_date));
        }
        
        // Generate PDF
        $pdf = Pdf::loadView('faculty.teaching-load.pdf', compact(
            'faculty',
            'schedules',
            'educationalQualifications',
            'assignmentsByType',
            'totalContactHours',
            'totalUnits',
            'excessLoadDisplay',
            'numberOfPreparations',
            'totalWorkloadPerDay',
            'schoolYear',
            'semester',
            'campusDirector',
            'vicePresident',
            'dateEffective'
        ));
        
        $fileName = 'Teaching_Load_' . str_replace(' ', '_', $faculty->name) . '_' . $schoolYear . '_' . $semester . '.pdf';
        
        return $pdf->download($fileName);
    }
}
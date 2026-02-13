<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\EducationalBackground;
use App\Models\Faculty;
use App\Models\FacultySubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TeachingLoadController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get faculty details from faculty table using faculty_code
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->first();
        
        // If no faculty record exists, return error
        if (!$faculty) {
            abort(404, 'Faculty record not found');
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
        
        // IMPROVED: Fetch schedules directly from schedules table
        $schedules = Schedule::where('faculty_code', $faculty->faculty_code)
            ->where('is_active', true)
            ->where('academic_year', $schoolYear)
            ->where('semester', $semester)
            ->with([
                'subject' => function($q) {
                    $q->select('id', 'course_code', 'subject_name', 'lecture_units', 'laboratory_units', 'program_id');
                },
                'subject.program' => function($q) {
                    $q->select('id', 'code', 'name');
                },
                'classroom' => function($q) {
                    $q->select('id', 'room_name', 'building');
                },
                'program' => function($q) {
                    $q->select('id', 'code', 'name');
                }
            ])
            ->orderBy('day', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Get faculty subjects for unit reference (optional - for override values)
        $facultySubjects = FacultySubject::where('faculty_code', $faculty->faculty_code)
            ->where('semester', $semester)
            ->with('subject')
            ->get()
            ->keyBy('subject_id'); // Key by subject_id for easy lookup
        
        // Attach faculty_subject data to each schedule for unit override if exists
        foreach ($schedules as $schedule) {
            if (isset($facultySubjects[$schedule->subject_id])) {
                $schedule->faculty_subject = $facultySubjects[$schedule->subject_id];
            }
        }
        
        // Get educational qualifications from educational_backgrounds table using faculty_code
        $educationalQualifications = EducationalBackground::where('faculty_code', $faculty->faculty_code)
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
        
        // IMPROVED: Calculate totals from schedules (avoiding double counting)
        $totalContactHours = 0;
        $totalUnits = 0;
        $uniqueSubjects = [];
        
        // Calculate contact hours and collect unique subjects
        foreach ($schedules as $schedule) {
            // Calculate contact hours for this schedule slot
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
            
            // Track unique subjects to avoid counting units multiple times
            // Key format: subject_id-year_level-section
            $uniqueKey = $schedule->subject_id . '-' . ($schedule->year_level ?? 'default') . '-' . ($schedule->section ?? 'default');
            
            if (!isset($uniqueSubjects[$uniqueKey])) {
                // Get units - priority: faculty_subject override > subject table
                $units = 0;
                if (isset($schedule->faculty_subject)) {
                    if ($schedule->faculty_subject->lecture_units !== null && $schedule->faculty_subject->laboratory_units !== null) {
                        $units = $schedule->faculty_subject->lecture_units + $schedule->faculty_subject->laboratory_units;
                    }
                }
                
                // Fallback to subject table if no override
                if ($units == 0 && $schedule->subject) {
                    $units = ($schedule->subject->lecture_units ?? 0) + ($schedule->subject->laboratory_units ?? 0);
                }
                
                $uniqueSubjects[$uniqueKey] = [
                    'subject_id' => $schedule->subject_id,
                    'units' => $units,
                    'subject_name' => $schedule->subject->subject_name ?? 'N/A',
                ];
                
                $totalUnits += $units;
            }
        }
        
        // Calculate excess load (assuming standard load is 21 units)
        $standardLoad = 21;
        $excessLoad = $totalUnits > $standardLoad ? ($totalUnits - $standardLoad) : 0;
        $excessLoadDisplay = $excessLoad > 0 ? number_format($excessLoad, 1) . ' units' : 'NONE';
        
        // Get number of unique subject preparations
        $numberOfPreparations = count(array_unique(array_column($uniqueSubjects, 'subject_id')));
        
        // Calculate average workload per day from schedules
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
            'dateEffective',
            'uniqueSubjects' // Pass this to the view for unit display
        ));
    }
    
    public function downloadPdf(Request $request)
    {
        $user = Auth::user();
        
        // Get faculty details from faculty table using faculty_code
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->first();
        
        // If no faculty record exists, return error
        if (!$faculty) {
            abort(404, 'Faculty record not found');
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
        
        // IMPROVED: Fetch schedules directly from schedules table
        $schedules = Schedule::where('faculty_code', $faculty->faculty_code)
            ->where('is_active', true)
            ->where('academic_year', $schoolYear)
            ->where('semester', $semester)
            ->with([
                'subject' => function($q) {
                    $q->select('id', 'course_code', 'subject_name', 'lecture_units', 'laboratory_units', 'program_id');
                },
                'subject.program' => function($q) {
                    $q->select('id', 'code', 'name');
                },
                'classroom' => function($q) {
                    $q->select('id', 'room_name', 'building');
                },
                'program' => function($q) {
                    $q->select('id', 'code', 'name');
                }
            ])
            ->orderBy('day', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Get faculty subjects for unit reference
        $facultySubjects = FacultySubject::where('faculty_code', $faculty->faculty_code)
            ->where('semester', $semester)
            ->with('subject')
            ->get()
            ->keyBy('subject_id');
        
        // Attach faculty_subject data to each schedule
        foreach ($schedules as $schedule) {
            if (isset($facultySubjects[$schedule->subject_id])) {
                $schedule->faculty_subject = $facultySubjects[$schedule->subject_id];
            }
        }
        
        // Get educational qualifications from educational_backgrounds table using faculty_code
        $educationalQualifications = EducationalBackground::where('faculty_code', $faculty->faculty_code)
            ->orderBy('year_graduated', 'desc')
            ->get();
        
        // Create empty administrative assignments array
        $assignmentsByType = [
            'Designation' => null,
            'Committee Work' => null,
            'Research Work' => null,
            'Extension' => null,
            'Production' => null,
        ];
        
        // Calculate totals from schedules (avoiding double counting)
        $totalContactHours = 0;
        $totalUnits = 0;
        $uniqueSubjects = [];
        
        foreach ($schedules as $schedule) {
            $start = strtotime($schedule->start_time);
            $end = strtotime($schedule->end_time);
            $hours = ($end - $start) / 3600;
            $totalContactHours += $hours;
            
            $uniqueKey = $schedule->subject_id . '-' . ($schedule->year_level ?? 'default') . '-' . ($schedule->section ?? 'default');
            
            if (!isset($uniqueSubjects[$uniqueKey])) {
                $units = 0;
                if (isset($schedule->faculty_subject)) {
                    if ($schedule->faculty_subject->lecture_units !== null && $schedule->faculty_subject->laboratory_units !== null) {
                        $units = $schedule->faculty_subject->lecture_units + $schedule->faculty_subject->laboratory_units;
                    }
                }
                
                if ($units == 0 && $schedule->subject) {
                    $units = ($schedule->subject->lecture_units ?? 0) + ($schedule->subject->laboratory_units ?? 0);
                }
                
                $uniqueSubjects[$uniqueKey] = [
                    'subject_id' => $schedule->subject_id,
                    'units' => $units,
                    'subject_name' => $schedule->subject->subject_name ?? 'N/A',
                ];
                
                $totalUnits += $units;
            }
        }
        
        $standardLoad = 21;
        $excessLoad = $totalUnits > $standardLoad ? ($totalUnits - $standardLoad) : 0;
        $excessLoadDisplay = $excessLoad > 0 ? number_format($excessLoad, 1) . ' units' : 'NONE';
        
        $numberOfPreparations = count(array_unique(array_column($uniqueSubjects, 'subject_id')));
        
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
        
        // Get system settings
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
            'dateEffective',
            'uniqueSubjects'
        ));
        
        $fileName = 'Teaching_Load_' . str_replace(' ', '_', $faculty->name) . '_' . $schoolYear . '_' . $semester . '.pdf';
        
        return $pdf->download($fileName);
    }
}
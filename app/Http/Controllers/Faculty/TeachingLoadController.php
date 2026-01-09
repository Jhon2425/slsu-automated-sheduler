<?php

// File: app/Http/Controllers/Faculty/TeachingLoadController.php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TeachingLoadController extends Controller
{
    public function index(Request $request)
    {
        $faculty = Auth::user();
        
        // Get current academic period or from request
        $schoolYear = $request->get('school_year', SystemSetting::getCurrentAcademicYear());
        $semester = $request->get('semester', SystemSetting::getCurrentSemester());
        
        // Get faculty's schedules with relationships
        $schedules = $faculty->schedules()
            ->where('is_active', true)
            ->where('academic_year', $schoolYear)
            ->where('semester', $semester)
            ->with(['subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
        
        // Get educational qualifications - CORRECTED
        $educationalQualifications = $faculty->educationalQualifications;
        
        // Get administrative assignments and group by type - CORRECTED
        $administrativeAssignments = $faculty->administrativeAssignments()
            ->active()
            ->forSemester($schoolYear, $semester)
            ->get();
        
        // Group administrative assignments by type for the table
        $assignmentsByType = [
            'Designation' => $administrativeAssignments->where('assignment_type', 'Designation')->first(),
            'Committee Work' => $administrativeAssignments->where('assignment_type', 'Committee Work')->first(),
            'Research Work' => $administrativeAssignments->where('assignment_type', 'Research Work')->first(),
            'Extension' => $administrativeAssignments->where('assignment_type', 'Extension')->first(),
            'Production' => $administrativeAssignments->where('assignment_type', 'Production')->first(),
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
            
            // Calculate units
            $units = ($schedule->lecture_units ?? 0) + ($schedule->laboratory_units ?? 0);
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
        
        // Get system settings for officials
        $campusDirector = SystemSetting::getCampusDirector();
        $vicePresident = SystemSetting::getVicePresident();
        $dateEffective = SystemSetting::get('default_date_effective');
        
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
        
        return view('faculty.teaching-load.index', compact(
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
        $faculty = Auth::user();
        
        // Get current academic period or from request
        $schoolYear = $request->get('school_year', SystemSetting::getCurrentAcademicYear());
        $semester = $request->get('semester', SystemSetting::getCurrentSemester());
        
        // Get all the same data as index method
        $schedules = $faculty->schedules()
            ->where('is_active', true)
            ->where('academic_year', $schoolYear)
            ->where('semester', $semester)
            ->with(['subject', 'classroom'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
        
        $educationalQualifications = $faculty->educationalQualifications;
        
        $administrativeAssignments = $faculty->administrativeAssignments()
            ->active()
            ->forSemester($schoolYear, $semester)
            ->get();
        
        $assignmentsByType = [
            'Designation' => $administrativeAssignments->where('assignment_type', 'Designation')->first(),
            'Committee Work' => $administrativeAssignments->where('assignment_type', 'Committee Work')->first(),
            'Research Work' => $administrativeAssignments->where('assignment_type', 'Research Work')->first(),
            'Extension' => $administrativeAssignments->where('assignment_type', 'Extension')->first(),
            'Production' => $administrativeAssignments->where('assignment_type', 'Production')->first(),
        ];
        
        // Calculate totals (same as index)
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
        
        $campusDirector = SystemSetting::getCampusDirector();
        $vicePresident = SystemSetting::getVicePresident();
        $dateEffective = SystemSetting::get('default_date_effective');
        
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
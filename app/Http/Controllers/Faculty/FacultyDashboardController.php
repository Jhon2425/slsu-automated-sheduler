<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\FacultySubject;
use App\Models\EducationalBackground;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FacultyDashboardController extends Controller
{
    /**
     * Resolve units — mirrors TeachingLoadController::resolveUnits()
     */
    private function resolveUnits($pivot, $subject = null): float
    {
        if ($pivot) {
            $l = $pivot->lecture_units    ?? null;
            $b = $pivot->laboratory_units ?? null;
            if ($l !== null || $b !== null) return (float)($l ?? 0) + (float)($b ?? 0);
            if (isset($pivot->units))        return (float)$pivot->units;
            if (isset($pivot->credit_units)) return (float)$pivot->credit_units;
            if (isset($pivot->credit_hours)) return (float)$pivot->credit_hours;
        }

        if ($subject) {
            if (isset($subject->lecture_units) || isset($subject->laboratory_units)) {
                return (float)($subject->lecture_units ?? 0) + (float)($subject->laboratory_units ?? 0);
            }
            if (isset($subject->units))        return (float)$subject->units;
            if (isset($subject->credit_units)) return (float)$subject->credit_units;
        }

        return 0;
    }

    /**
     * Normalize semester strings — mirrors TeachingLoadController::normalizeSemester()
     */
    private function normalizeSemester(string $semester): string
    {
        $s = strtolower(trim($semester));

        if (in_array($s, ['1', '1st', 'first', '1st semester'], true))                     return '1st';
        if (in_array($s, ['2', '2nd', 'second', '2nd semester'], true))                    return '2nd';
        if (in_array($s, ['3', 'summer', 'midyear', 'mid-year', 'summer semester'], true)) return 'Summer';

        return $semester;
    }

    /**
     * All realistic stored variants — mirrors TeachingLoadController::semesterVariants()
     */
    private function semesterVariants(string $semester): array
    {
        $map = [
            '1st'    => ['1st', '1', 'First', 'first', '1st Semester', '1st semester', 'First Semester'],
            '2nd'    => ['2nd', '2', 'Second', 'second', '2nd Semester', '2nd semester', 'Second Semester'],
            'Summer' => ['Summer', 'summer', '3', 'Midyear', 'midyear', 'Mid-year', 'Summer Semester'],
        ];

        return $map[$this->normalizeSemester($semester)] ?? [$semester];
    }

    /**
     * Fetch schedules — mirrors TeachingLoadController::fetchSchedules()
     */
    private function fetchSchedules(Faculty $faculty, string $schoolYear, string $semester)
    {
        $base = Schedule::where('faculty_code', $faculty->faculty_code)
            ->where('is_active', true)
            ->with(['subject', 'subject.program', 'classroom', 'program'])
            ->orderBy('day')
            ->orderBy('start_time');

        // Try school_year + semester first
        $schedules = (clone $base)
            ->where('academic_year', $schoolYear)
            ->whereIn('semester', $this->semesterVariants($semester))
            ->get();

        if ($schedules->isNotEmpty()) return $schedules;

        // Fall back to school_year only
        $schedules = (clone $base)->where('academic_year', $schoolYear)->get();
        if ($schedules->isNotEmpty()) return $schedules;

        // Fall back to all active
        return (clone $base)->get();
    }

    /**
     * Fetch assigned subjects — mirrors TeachingLoadController::fetchAssignedSubjects()
     */
    private function fetchAssignedSubjects(Faculty $faculty)
    {
        return FacultySubject::where('faculty_code', $faculty->faculty_code)
            ->with(['subject', 'subject.program'])
            ->get();
    }

    /**
     * Get current academic period
     */
    private function getAcademicPeriod(): array
    {
        $currentYear  = (int) date('Y');
        $currentMonth = (int) date('n');

        if ($currentMonth >= 6) {
            $schoolYear = "{$currentYear}-" . ($currentYear + 1);
        } else {
            $schoolYear = ($currentYear - 1) . "-{$currentYear}";
        }

        $academicYear = $schoolYear;

        if ($currentMonth >= 6 && $currentMonth <= 10)     $semester = '1st';
        elseif ($currentMonth >= 11 || $currentMonth <= 3) $semester = '2nd';
        else                                                $semester = 'Summer';

        return compact('academicYear', 'schoolYear', 'semester');
    }

    /**
     * Resolve faculty record from authenticated user
     */
    private function resolveFaculty(): Faculty
    {
        $user = Auth::user();
        return Faculty::where('faculty_code', $user->faculty_code)->firstOrFail();
    }

    /**
     * Compute totals using the SAME logic as TeachingLoadController::resolveData()
     *
     * - totalContactHours: sum of (end - start) / 3600 for EVERY schedule row
     * - totalUnits: counted once per unique subject_id + year_level + section key
     * - workloadPerDay: grouped by day_name, then averaged
     */
    private function computeTotals(Faculty $faculty, string $schoolYear, string $semester): array
    {
        $assignedSubjects = $this->fetchAssignedSubjects($faculty);
        $schedules        = $this->fetchSchedules($faculty, $schoolYear, $semester);

        $schedulesBySubjectId = $schedules->groupBy('subject_id');

        $totalContactHours = 0;
        $totalUnits        = 0;
        $uniqueSubjects    = [];
        $workloadPerDay    = [];

        foreach ($assignedSubjects as $fs) {
            $subjectSchedules = $schedulesBySubjectId->get($fs->subject_id, collect());
            $units            = $this->resolveUnits($fs, $fs->subject ?? null);

            if ($subjectSchedules->isNotEmpty()) {
                foreach ($subjectSchedules as $schedule) {
                    $start        = strtotime($schedule->start_time);
                    $end          = strtotime($schedule->end_time);
                    $contactHours = ($end - $start) / 3600;

                    $totalContactHours += $contactHours;

                    // Workload per day — grouped by day_name
                    $dayName = $schedule->day_name ?? null;
                    if ($dayName) {
                        $workloadPerDay[$dayName] = ($workloadPerDay[$dayName] ?? 0) + $contactHours;
                    }
                }
            }

            // Count units only once per unique subject+year_level+section
            $key = ($fs->subject->id ?? 'x')
                 . '-' . ($fs->year_level ?: 'default')
                 . '-' . ($fs->section    ?: 'default');

            if (!isset($uniqueSubjects[$key])) {
                $uniqueSubjects[$key] = true;
                $totalUnits += $units;
            }
        }

        $standardLoad      = 21;
        $excessLoad        = max(0, $totalUnits - $standardLoad);
        $excessLoadDisplay = $excessLoad > 0 ? number_format($excessLoad, 1) . ' units' : 'NONE';

        $totalWorkloadPerDay = !empty($workloadPerDay)
            ? number_format(array_sum($workloadPerDay) / count($workloadPerDay), 2) . ' hours/day'
            : 'Not set';

        return [
            'assignedSubjects'  => $assignedSubjects,
            'schedules'         => $schedules,
            'totalContactHours' => round($totalContactHours, 2),
            'totalUnits'        => $totalUnits,
            'excessLoad'        => $excessLoadDisplay,
            'totalWorkloadPerDay' => $totalWorkloadPerDay,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $faculty = $this->resolveFaculty();

        $period       = $this->getAcademicPeriod();
        $academicYear = $period['academicYear'];
        $schoolYear   = $period['schoolYear'];
        $semester     = $period['semester'];

        $totals = $this->computeTotals($faculty, $schoolYear, $semester);

        $educationalQualifications = EducationalBackground::where('faculty_code', $faculty->faculty_code)
            ->orderByDesc('year_graduated')
            ->get();

        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident  = config('app.vice_president',  'GONDELINA A. MADOVAN, PhD');
        $dateEffective  = date('F d, Y', strtotime(config('app.default_date_effective', 'August 30, 2023')));

        return view('faculty.dashboard', array_merge($totals, compact(
            'faculty',
            'academicYear',
            'schoolYear',
            'semester',
            'educationalQualifications',
            'campusDirector',
            'vicePresident',
            'dateEffective'
        )));
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * View schedule - Shows the faculty's complete teaching load document
     */
    public function viewSchedule()
    {
        $faculty = $this->resolveFaculty();

        $period       = $this->getAcademicPeriod();
        $academicYear = $period['academicYear'];
        $schoolYear   = $period['schoolYear'];
        $semester     = $period['semester'];

        $totals = $this->computeTotals($faculty, $schoolYear, $semester);

        $educationalQualifications = EducationalBackground::where('faculty_code', $faculty->faculty_code)
            ->orderByDesc('year_graduated')
            ->get();

        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident  = config('app.vice_president',  'GONDELINA A. MADOVAN, PhD');
        $dateEffective  = date('F d, Y', strtotime(config('app.default_date_effective', 'August 30, 2023')));

        return view('faculty.teaching-load', array_merge($totals, compact(
            'faculty',
            'academicYear',
            'schoolYear',
            'semester',
            'educationalQualifications',
            'campusDirector',
            'vicePresident',
            'dateEffective'
        )));
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Download teaching load as PDF
     */
    public function downloadPDF()
    {
        $faculty = $this->resolveFaculty();

        $period       = $this->getAcademicPeriod();
        $academicYear = $period['academicYear'];
        $schoolYear   = $period['schoolYear'];
        $semester     = $period['semester'];

        $totals = $this->computeTotals($faculty, $schoolYear, $semester);

        $educationalQualifications = EducationalBackground::where('faculty_code', $faculty->faculty_code)
            ->orderByDesc('year_graduated')
            ->get();

        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident  = config('app.vice_president',  'GONDELINA A. MADOVAN, PhD');
        $dateEffective  = date('F d, Y', strtotime(config('app.default_date_effective', 'August 30, 2023')));

        $pdf = \PDF::loadView('faculty.teaching-load-pdf', array_merge($totals, compact(
            'faculty',
            'academicYear',
            'schoolYear',
            'semester',
            'educationalQualifications',
            'campusDirector',
            'vicePresident',
            'dateEffective'
        )));

        return $pdf->download('teaching-load-' . $faculty->name . '.pdf');
    }
}
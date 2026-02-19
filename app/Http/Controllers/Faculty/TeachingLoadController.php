<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\EducationalBackground;
use App\Models\Faculty;
use App\Models\FacultySubject;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TeachingLoadController extends Controller
{
    /**
     * Normalize semester strings so "1st", "1", "First", "1st Semester" all match.
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
     * All realistic stored variants for a semester value.
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
     * Resolve units — checks many possible column names gracefully.
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
     * Fetch the faculty's program/course using multiple resolution strategies.
     *
     * Priority:
     *   1. programs() relationship via faculty_enrollments pivot
     *   2. Direct program_id on the user record
     *   3. From subject.program eager-loaded on assigned subjects
     */
    private function resolveFacultyPrograms(User $user, $assignedSubjects): \Illuminate\Support\Collection
    {
        // 1. Via faculty_enrollments pivot → programs table
        if ($user->programs && $user->programs->isNotEmpty()) {
            return $user->programs;
        }

        // 2. Direct program_id on user record
        if (!empty($user->program_id)) {
            $program = Program::find($user->program_id);
            if ($program) return collect([$program]);
        }

        // 3. From subject.program eager-loaded on faculty_subjects
        $programs = $assignedSubjects
            ->map(fn ($fs) => $fs->subject->program ?? null)
            ->filter()
            ->unique('id')
            ->values();

        return $programs;
    }

    /**
     * Fetch ALL assigned subjects for this faculty using faculty_code only.
     * Eager-loads the subject's program so we can fall back to it.
     */
    private function fetchAssignedSubjects(Faculty $faculty)
    {
        return FacultySubject::where('faculty_code', $faculty->faculty_code)
            ->with([
                'subject',
                'subject.program',   // subject's own program relationship
            ])
            ->get();
    }

    /**
     * Fetch ALL schedules for this faculty by faculty_code.
     * Tries school_year + semester → school_year only → all active.
     */
    private function fetchSchedules(Faculty $faculty, string $schoolYear, string $semester)
    {
        $base = Schedule::where('faculty_code', $faculty->faculty_code)
            ->where('is_active', true)
            ->with(['subject', 'subject.program', 'classroom', 'program'])
            ->orderBy('day')
            ->orderBy('start_time');

        $schedules = (clone $base)
            ->where('academic_year', $schoolYear)
            ->whereIn('semester', $this->semesterVariants($semester))
            ->get();

        if ($schedules->isNotEmpty()) return $schedules;

        $schedules = (clone $base)->where('academic_year', $schoolYear)->get();
        if ($schedules->isNotEmpty()) return $schedules;

        return (clone $base)->get();
    }

    /**
     * Extract a display label from a Program model.
     */
    private function programLabel(Program $prog): string
    {
        return $prog->code ?? $prog->program_name ?? $prog->name ?? 'N/A';
    }

    /**
     * Resolve the best program code for a display row.
     *
     * Priority:
     *   1. Program fetched directly from programs table via user->program_id (FK)
     *   2. Programs linked via faculty_enrollments pivot → programs table
     *   3. Subject's own program via faculty_subject eager load
     *   4. Schedule's subject program or schedule's program relation
     */
    private function resolveProgramCode($fs, $schedule, $linkedProgram, $userPrograms): string
    {
        // 1. Direct FK: user->program_id → programs table
        if ($linkedProgram) return $this->programLabel($linkedProgram);

        // 2. Via faculty_enrollments pivot → programs table
        if ($userPrograms->isNotEmpty()) {
            return $this->programLabel($userPrograms->first());
        }

        // 3. Subject's own program (eager-loaded on faculty_subject)
        if (!empty($fs->subject->program->code)) return $fs->subject->program->code;
        if (!empty($fs->subject->program->name)) return $fs->subject->program->name;

        // 4. Schedule's subject program or schedule's program relation
        if ($schedule) {
            if (!empty($schedule->subject->program->code)) return $schedule->subject->program->code;
            if (!empty($schedule->program->code))          return $schedule->program->code;
            if (!empty($schedule->program->name))          return $schedule->program->name;
        }

        return 'N/A';
    }

    /**
     * Resolve all shared data.
     */
    private function resolveData(Request $request, Faculty $faculty): array
    {
        $currentYear  = (int) date('Y');
        $currentMonth = (int) date('n');

        $defaultSchoolYear = $currentMonth >= 6
            ? "{$currentYear}-" . ($currentYear + 1)
            : ($currentYear - 1) . "-{$currentYear}";

        if ($currentMonth >= 6 && $currentMonth <= 10)     $defaultSemester = '1st';
        elseif ($currentMonth >= 11 || $currentMonth <= 3) $defaultSemester = '2nd';
        else                                                $defaultSemester = 'Summer';

        $schoolYear = $request->get('school_year', $defaultSchoolYear);
        $semester   = $request->get('semester', $defaultSemester);

        // ── Fetch the User record linked to this faculty ──────────────────
        $user = User::where('faculty_code', $faculty->faculty_code)
            ->with([
                'programs',        // via faculty_enrollments pivot
                'facultySubjects', // direct faculty_subject rows
            ])
            ->first();

        // Resolve Program model directly from programs table via user->program_id (FK)
        $linkedProgram = ($user && !empty($user->program_id))
            ? Program::find($user->program_id)
            : null;

        // ── Step 1: Fetch ALL assigned subjects by faculty_code ───────────
        $assignedSubjects = $this->fetchAssignedSubjects($faculty);

        // ── Step 2: Resolve faculty programs ─────────────────────────────
        $userPrograms = $user
            ? $this->resolveFacultyPrograms($user, $assignedSubjects)
            : collect();

        // ── Step 3: Fetch ALL schedules by faculty_code ───────────────────
        $schedules            = $this->fetchSchedules($faculty, $schoolYear, $semester);
        $schedulesBySubjectId = $schedules->groupBy('subject_id');

        // ── Step 4: Build display rows ────────────────────────────────────
        $displayRows = [];

        foreach ($assignedSubjects as $fs) {
            $subjectSchedules = $schedulesBySubjectId->get($fs->subject_id, collect());
            $units            = $this->resolveUnits($fs, $fs->subject ?? null);

            if ($subjectSchedules->isNotEmpty()) {
                foreach ($subjectSchedules as $schedule) {
                    $start        = strtotime($schedule->start_time);
                    $end          = strtotime($schedule->end_time);
                    $contactHours = ($end - $start) / 3600;

                    $displayRows[] = [
                        'fs'            => $fs,
                        'schedule'      => $schedule,
                        'subject'       => $fs->subject ?? $schedule->subject,
                        'units'         => $units,
                        'contact_hours' => $contactHours,
                        'time_start'    => $start,
                        'time_end'      => $end,
                        'day_name'      => $schedule->day_name,
                        'room'          => $schedule->classroom->room_name
                                           ?? $schedule->classroom->name
                                           ?? 'N/A',
                        'program_code'  => $this->resolveProgramCode($fs, $schedule, $linkedProgram, $userPrograms),
                        'year_level'    => $fs->year_level ?? $schedule->year_level ?? '',
                        'section'       => $fs->section   ?? $schedule->section   ?? $schedule->year_section ?? '',
                        'class_size'    => $fs->class_size ?? $schedule->class_size ?? 0,
                        'has_schedule'  => true,
                    ];
                }
            } else {
                $displayRows[] = [
                    'fs'            => $fs,
                    'schedule'      => null,
                    'subject'       => $fs->subject,
                    'units'         => $units,
                    'contact_hours' => 0,
                    'time_start'    => null,
                    'time_end'      => null,
                    'day_name'      => null,
                    'room'          => null,
                    'program_code'  => $this->resolveProgramCode($fs, null, $linkedProgram, $userPrograms),
                    'year_level'    => $fs->year_level ?? '',
                    'section'       => $fs->section ?? '',
                    'class_size'    => $fs->class_size ?? 0,
                    'has_schedule'  => false,
                ];
            }
        }

        // ── Step 5: Totals ────────────────────────────────────────────────
        $totalContactHours = 0;
        $totalUnits        = 0;
        $uniqueSubjects    = [];

        foreach ($displayRows as $row) {
            $totalContactHours += $row['contact_hours'];

            $key = ($row['subject']->id ?? 'x')
                 . '-' . ($row['year_level'] ?: 'default')
                 . '-' . ($row['section']    ?: 'default');

            if (!isset($uniqueSubjects[$key])) {
                $uniqueSubjects[$key] = [
                    'subject_id'   => $row['subject']->id ?? null,
                    'units'        => $row['units'],
                    'subject_name' => $row['subject']->subject_name ?? 'N/A',
                ];
                $totalUnits += $row['units'];
            }
        }

        $standardLoad      = 21;
        $excessLoad        = max(0, $totalUnits - $standardLoad);
        $excessLoadDisplay = $excessLoad > 0 ? number_format($excessLoad, 1) . ' units' : 'NONE';

        $numberOfPreparations = count(array_unique(array_filter(array_column($uniqueSubjects, 'subject_id'))));

        $workloadPerDay = [];
        foreach ($displayRows as $row) {
            if ($row['has_schedule'] && $row['day_name']) {
                $workloadPerDay[$row['day_name']] = ($workloadPerDay[$row['day_name']] ?? 0) + $row['contact_hours'];
            }
        }
        $totalWorkloadPerDay = !empty($workloadPerDay)
            ? number_format(array_sum($workloadPerDay) / count($workloadPerDay), 2) . ' hours'
            : 'Not set';

        // ── Officials ─────────────────────────────────────────────────────
        $campusDirector = config('app.campus_director', 'ALMA J. CARINGAL');
        $vicePresident  = config('app.vice_president',  'GONDELINA A. MADOVAN, PhD');

        $rawDate = config('app.default_date_effective', 'August 30, 2023');
        try   { $dateEffective = date('F d, Y', strtotime($rawDate)); }
        catch (\Throwable) { $dateEffective = 'August 30, 2023'; }

        if ($faculty->appointment_date) {
            $faculty->formatted_appointment_date = date('F Y', strtotime($faculty->appointment_date));
        }

        // ── Educational qualifications ────────────────────────────────────
        $educationalQualifications = EducationalBackground::where('faculty_code', $faculty->faculty_code)
            ->orderByDesc('year_graduated')
            ->get();

        // ── Administrative assignments (stub) ─────────────────────────────
        $assignmentsByType = [
            'Designation'    => null,
            'Committee Work' => null,
            'Research Work'  => null,
            'Extension'      => null,
            'Production'     => null,
        ];

        return compact(
            'faculty', 'user', 'userPrograms',
            'displayRows', 'schedules', 'assignedSubjects',
            'educationalQualifications', 'assignmentsByType',
            'totalContactHours', 'totalUnits', 'excessLoadDisplay',
            'numberOfPreparations', 'totalWorkloadPerDay',
            'schoolYear', 'semester',
            'campusDirector', 'vicePresident', 'dateEffective',
            'uniqueSubjects'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user    = Auth::user();
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->firstOrFail();

        return view('faculty.teaching-load', $this->resolveData($request, $faculty));
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function downloadPdf(Request $request)
    {
        $user    = Auth::user();
        $faculty = Faculty::where('faculty_code', $user->faculty_code)->firstOrFail();

        $data = $this->resolveData($request, $faculty);

        $pdf = Pdf::loadView('faculty.teaching-load.pdf', $data)
            ->setPaper('A4', 'portrait');

        $fileName = 'Teaching_Load_'
            . str_replace(' ', '_', $faculty->name)
            . '_' . $data['schoolYear']
            . '_' . $data['semester']
            . '.pdf';

        return $pdf->download($fileName);
    }
}
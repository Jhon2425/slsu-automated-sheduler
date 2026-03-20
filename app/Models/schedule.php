<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'faculty_code',
        'subject_id',
        'classroom_id',
        'program_id',
        'day',
        'day_name',
        'start_time',
        'end_time',
        'schedule_date',
        'class_type',
        'semester',
        'year_level',
        'year_section',
        'section',
        'academic_year',
        'hours',
        'ojt_hours',         // raw decimal stored from faculty_subject
        'ojt_hours_rounded', // rounded integer for timetable display
        'is_active',
    ];

    protected $casts = [
        'schedule_date'    => 'date',
        'is_active'        => 'boolean',
        'ojt_hours'        => 'decimal:4', // preserve full precision
        'ojt_hours_rounded'=> 'integer',
        'hours'            => 'integer',
    ];

    // Map day numbers to names
    private static $dayNumberToName = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    ];

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get the day name from day number or direct day_name field.
     */
    public function getDayNameAttribute()
    {
        if (isset($this->attributes['day_name']) && $this->attributes['day_name']) {
            return $this->attributes['day_name'];
        }

        if (isset($this->attributes['day'])) {
            return self::$dayNumberToName[$this->attributes['day']] ?? 'Monday';
        }

        return 'Monday';
    }

    /**
     * Whether this schedule is an OJT session.
     */
    public function getIsOjtAttribute(): bool
    {
        return strtolower($this->attributes['class_type'] ?? '') === 'ojt';
    }

    /**
     * Get the OJT hours rounded for timetable display.
     *
     * Rule: standard PHP round() — < .5 rounds down, >= .5 rounds up.
     * Minimum 1 so an OJT subject always shows at least 1 hour.
     * Returns null for non-OJT subjects.
     */
    public function getOjtHoursDisplayAttribute(): ?int
    {
        if (!$this->is_ojt) {
            return null;
        }

        $raw = isset($this->attributes['ojt_hours'])
            ? (float) $this->attributes['ojt_hours']
            : null;

        if ($raw === null || $raw <= 0) {
            return null;
        }

        return max(1, (int) round($raw));
    }

    /**
     * Get faculty name — prevents N/A display.
     */
    public function getFacultyNameAttribute(): string
    {
        return $this->faculty ? $this->faculty->name : 'N/A';
    }

    /**
     * Get faculty code — prefers denormalised column, falls back to relationship.
     */
    public function getFacultyCodeAttribute(): string
    {
        if (isset($this->attributes['faculty_code']) && $this->attributes['faculty_code']) {
            return $this->attributes['faculty_code'];
        }

        return $this->faculty ? $this->faculty->faculty_code : 'N/A';
    }

    /**
     * Get subject name — prevents N/A display.
     */
    public function getSubjectNameAttribute(): string
    {
        return $this->subject ? $this->subject->subject_name : 'N/A';
    }

    /**
     * Get course code — prevents N/A display.
     */
    public function getCourseCodeAttribute(): string
    {
        return $this->subject ? $this->subject->course_code : 'N/A';
    }

    /**
     * Get classroom name — prevents N/A display.
     */
    public function getClassroomNameAttribute(): string
    {
        if (!$this->classroom) {
            return 'N/A';
        }

        return $this->classroom->room_name
            ?? $this->classroom->name
            ?? 'Room ' . $this->classroom->id;
    }

    /**
     * Get program name — prevents N/A display.
     */
    public function getProgramNameAttribute(): string
    {
        return $this->program ? $this->program->program_name : 'N/A';
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Load all relationships at once.
     * Usage: Schedule::withAllRelations()->get()
     */
    public function scopeWithAllRelations($query)
    {
        return $query->with(['faculty', 'subject', 'classroom', 'program']);
    }

    /**
     * Active schedules only.
     * Usage: Schedule::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by faculty code.
     * Usage: Schedule::byFacultyCode('FAC-001')->get()
     */
    public function scopeByFacultyCode($query, $facultyCode)
    {
        return $query->where('faculty_code', $facultyCode);
    }

    /**
     * OJT sessions only.
     * Usage: Schedule::ojt()->get()
     */
    public function scopeOjt($query)
    {
        return $query->whereRaw('LOWER(class_type) = ?', ['ojt']);
    }

    /**
     * Regular (non-OJT) sessions only.
     * Usage: Schedule::regular()->get()
     */
    public function scopeRegular($query)
    {
        return $query->whereRaw('LOWER(class_type) != ?', ['ojt']);
    }
}
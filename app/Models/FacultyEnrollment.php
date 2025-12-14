<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyEnrollment extends Model
{
    use HasFactory;

    protected $table = 'faculty_enrollments';

    protected $fillable = [
        // relationships
        'faculty_id',        // user_id acting as faculty
        'program_id',
        'course_subject',    // subject_id

        // enrollment details
        'year_section',
        'no_of_students',
        'units',
        'no_of_hours',
        'action_type',

        // status
        'enrollment_status', // pending | active | declined | completed
    ];

    protected $casts = [
        'no_of_hours'    => 'integer',
        'no_of_students' => 'integer',
        'units'          => 'integer',
    ];

    /* =====================================================
     | RELATIONSHIPS
     ===================================================== */

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'course_subject');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'faculty_enrollment_id');
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class, 'faculty_enrollment_id');
    }

    /* =====================================================
     | SCOPES
     ===================================================== */

    public function scopePending($query)
    {
        return $query->where('enrollment_status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeDeclined($query)
    {
        return $query->where('enrollment_status', 'declined');
    }

    public function scopeCompleted($query)
    {
        return $query->where('enrollment_status', 'completed');
    }

    /* =====================================================
     | STATUS HELPERS
     ===================================================== */

    public function accept(): void
    {
        $this->update(['enrollment_status' => 'active']);
    }

    public function decline(): void
    {
        $this->update(['enrollment_status' => 'declined']);
    }

    /* =====================================================
     | SCHEDULING HELPERS
     ===================================================== */

    /**
     * Return units, defaulting to no_of_hours if null
     */
    public function getUnitsAttribute($value)
    {
        return $value ?? $this->no_of_hours;
    }

    public function hasConflictOn(string $day, string $startTime, string $endTime): bool
    {
        return $this->schedules()
            ->where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->exists();
    }

    public function getTotalScheduledHoursAttribute(): int
    {
        return (int) $this->schedules()->sum('duration');
    }

    public function getRemainingHoursAttribute(): int
    {
        return max(0, $this->no_of_hours - $this->total_scheduled_hours);
    }

    public function hasCompletedHours(): bool
    {
        return $this->remaining_hours === 0;
    }
}

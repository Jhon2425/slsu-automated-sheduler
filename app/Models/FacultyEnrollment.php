<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'program_id',
        'course_subject',
        'year_section',
        'no_of_students',
        'units',
        'no_of_hours',
        'action_type',
        'enrollment_status', // pending, active, declined, completed
    ];

    /**
     * The faculty (user) who is enrolled
     */
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    /**
     * The program this enrollment belongs to
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * The schedules assigned to this enrollment
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'faculty_enrollment_id');
    }

    /**
     * Scope to filter only pending enrollments
     */
    public function scopePending($query)
    {
        return $query->where('enrollment_status', 'pending');
    }

    /**
     * Scope to filter only active enrollments
     */
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    /**
     * Scope to filter only declined enrollments
     */
    public function scopeDeclined($query)
    {
        return $query->where('enrollment_status', 'declined');
    }

    /**
     * Mark this enrollment as accepted
     */
    public function accept()
    {
        $this->update(['enrollment_status' => 'active']);
    }

    /**
     * Mark this enrollment as declined
     */
    public function decline()
    {
        $this->update(['enrollment_status' => 'declined']);
    }
}

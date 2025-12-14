<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description', 'semester', 'academic_year', 'status', 'admin_id'
    ];

    public function enrollments()
    {
        return $this->hasMany(FacultyEnrollment::class);
    }

    public function faculties()
    {
        return $this->belongsToMany(
            User::class,
            'faculty_enrollments',
            'program_id',
            'faculty_id'
        )->withPivot(
            'enrollment_status', 'course_subject', 'year_section',
            'no_of_students', 'units', 'no_of_hours', 'action_type'
        )->withTimestamps();
    }

    public function pendingEnrollments()
    {
        return $this->hasMany(FacultyEnrollment::class)->where('enrollment_status', 'pending');
    }

    public function activeEnrollments()
    {
        return $this->hasMany(FacultyEnrollment::class)->where('enrollment_status', 'active');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Alias for faculty enrollments - used for schedule generation
     * This maps to faculty_enrollments table with active status
     */
    public function facultySubjects()
    {
        return $this->hasMany(FacultyEnrollment::class, 'program_id')
            ->where('enrollment_status', 'active');
    }

    /**
     * Get schedules for this program
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'program_id');
    }
}
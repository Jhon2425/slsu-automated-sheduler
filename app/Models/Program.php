<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ============= Program Model =============
class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'semester',
        'academic_year',
        'status',
    ];

    // Get all enrollments for this program
    public function enrollments()
    {
        return $this->hasMany(FacultyEnrollment::class);
    }

    // Get all faculty members enrolled in this program
    public function faculties()
    {
        return $this->belongsToMany(User::class, 'faculty_enrollments', 'program_id', 'faculty_id')
                    ->withPivot('enrollment_status', 'course_subject', 'year_section', 'no_of_students', 'units', 'no_of_hours', 'action_type')
                    ->withTimestamps();
    }

    // Get pending enrollments
    public function pendingEnrollments()
    {
        return $this->hasMany(FacultyEnrollment::class)->where('enrollment_status', 'pending');
    }

    // Get active enrollments
    public function activeEnrollments()
    {
        return $this->hasMany(FacultyEnrollment::class)->where('enrollment_status', 'active');
    }
}

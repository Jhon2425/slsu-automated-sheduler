<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_enrollment_id',
        'classroom_id',
        'day',
        'start_time',
        'end_time',
        'schedule_date',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    // Get the faculty enrollment
    public function facultyEnrollment()
    {
        return $this->belongsTo(FacultyEnrollment::class);
    }

    // Get the classroom
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Get the faculty directly through enrollment
    public function faculty()
    {
        return $this->hasOneThrough(
            User::class,
            FacultyEnrollment::class,
            'id',
            'id',
            'faculty_enrollment_id',
            'faculty_id'
        );
    }

    // Get the program directly through enrollment
    public function program()
    {
        return $this->hasOneThrough(
            Program::class,
            FacultyEnrollment::class,
            'id',
            'id',
            'faculty_enrollment_id',
            'program_id'
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
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
        'is_active'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'is_active' => 'boolean',
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

    /**
     * Get the day name from day number or direct day_name field
     * This accessor will work whether you store day as number or day_name as string
     */
    public function getDayNameAttribute()
    {
        // If day_name is already set in the database, use it
        if (isset($this->attributes['day_name']) && $this->attributes['day_name']) {
            return $this->attributes['day_name'];
        }
        
        // Otherwise, convert from day number
        if (isset($this->attributes['day'])) {
            return self::$dayNumberToName[$this->attributes['day']] ?? 'Monday';
        }
        
        return 'Monday';
    }

    /**
     * Relationships
     */
    public function faculty()
    {
        // Updated to use User model instead of Faculty model
        // since faculty data is stored in users table with faculty_code
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

    /**
     * Accessor to get faculty name - prevents N/A display
     */
    public function getFacultyNameAttribute()
    {
        return $this->faculty ? $this->faculty->name : 'N/A';
    }

    /**
     * Accessor to get faculty code - prevents N/A display
     */
    public function getFacultyCodeAttribute()
    {
        return $this->faculty ? $this->faculty->faculty_code : 'N/A';
    }

    /**
     * Accessor to get subject name - prevents N/A display
     */
    public function getSubjectNameAttribute()
    {
        return $this->subject ? $this->subject->subject_name : 'N/A';
    }

    /**
     * Accessor to get course code - prevents N/A display
     */
    public function getCourseCodeAttribute()
    {
        return $this->subject ? $this->subject->course_code : 'N/A';
    }

    /**
     * Accessor to get classroom name - prevents N/A display
     */
    public function getClassroomNameAttribute()
    {
        if (!$this->classroom) {
            return 'N/A';
        }
        
        return $this->classroom->room_name 
            ?? $this->classroom->name 
            ?? 'Room ' . $this->classroom->id;
    }

    /**
     * Accessor to get program name - prevents N/A display
     */
    public function getProgramNameAttribute()
    {
        return $this->program ? $this->program->program_name : 'N/A';
    }

    /**
     * Scope to get schedules with all relationships loaded
     * Usage: Schedule::withAllRelations()->get()
     */
    public function scopeWithAllRelations($query)
    {
        return $query->with(['faculty', 'subject', 'classroom', 'program']);
    }

    /**
     * Scope to get active schedules only
     * Usage: Schedule::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
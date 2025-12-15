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
        'start_time',
        'end_time',
        'schedule_date',
        'class_type',
        'semester',
        'year_level'
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
     * Get the day name from day number
     */
    public function getDayNameAttribute()
    {
        return self::$dayNumberToName[$this->day] ?? 'Monday';
    }

    /**
     * Relationships
     */
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
}
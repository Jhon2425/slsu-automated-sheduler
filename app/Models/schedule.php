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
        'day_name',      // Add this to support direct day_name storage
        'start_time',
        'end_time',
        'schedule_date',
        'class_type',
        'semester',
        'year_level',
        'year_section',  // Add this
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
        return $this->belongsTo(Faculty::class, 'faculty_id');
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
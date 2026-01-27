<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examination extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'subject_id',
        'classroom_id',
        'exam_date',
        'day',           // IMPORTANT: Add this field to fillable
        'start_time',
        'end_time',
        'exam_type',
        'year_section',
        'is_active',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_active' => 'boolean',
    ];

    // Map day numbers to day names
    protected $dayMap = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    // Accessor: Convert day number to day name
    public function getDayNameAttribute()
    {
        return $this->dayMap[$this->day] ?? 'Unknown';
    }

    // Relationships
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
}
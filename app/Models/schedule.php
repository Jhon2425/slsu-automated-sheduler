<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'classroom_id',
        'day',
        'start_time',
        'end_time',
        'schedule_date',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
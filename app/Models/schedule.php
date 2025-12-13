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
        'room_id',
        'program_id',
        'day',
        'start_time',
        'end_time',
        'class_type',
        'semester',
        'academic_year',
        'type',
        'is_confirmed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}

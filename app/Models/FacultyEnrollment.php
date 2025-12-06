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
        'enrollment_status',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'program_id', 'program_id');
    }
}

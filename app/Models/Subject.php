<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'course_code',
        'subject_name',
        'lec',
        'lab',
        'units',
        'ojt_hours',
        'pre_req',
        'semester',
        'year_level',
    ];

    protected $casts = [
        'units'      => 'decimal:1',
        'year_level' => 'integer',
        'lec'        => 'integer',
        'lab'        => 'integer',
        'ojt_hours'  => 'integer',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function faculties()
    {
        return $this->belongsToMany(
            Faculty::class,
            'faculty_subject',
            'subject_id',
            'faculty_id'
        )
        ->withPivot('program_id', 'lecture_units', 'laboratory_units', 'ojt_hours', 'year_level', 'semester', 'class_size')
        ->withTimestamps();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySubject extends Model
{
    use HasFactory;

    protected $table = 'faculty_subject';

    protected $fillable = [
        'faculty_code',
        'faculty_id',
        'subject_id',
        'program_id',
        'lecture_units',
        'laboratory_units',
        'year_level',
        'semester',
        'class_size',
    ];

    protected $casts = [
        'lecture_units' => 'float',
        'laboratory_units' => 'float',
        'class_size' => 'integer',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
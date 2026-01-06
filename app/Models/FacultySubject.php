<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySubject extends Model
{
    use HasFactory;

    protected $table = 'faculty_subjects';

    protected $fillable = [
        'faculty_id',
        'subject_id',
        'program_id',
        'lecture_units',
        'laboratory_units',
    ];

    protected $casts = [
        'lecture_units' => 'decimal:1',
        'laboratory_units' => 'decimal:1',
    ];

    /**
     * Get the faculty for this assignment
     */
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    /**
     * Get the subject for this assignment
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the program for this assignment
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get total units
     */
    public function getTotalUnitsAttribute()
    {
        return ($this->lecture_units ?? 0) + ($this->laboratory_units ?? 0);
    }
}
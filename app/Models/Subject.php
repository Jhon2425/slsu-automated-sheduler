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
        'units',
        'semester',
        'year_level',
        'enrolled_student', // ✅ ADDED
    ];

    protected $casts = [
        'units' => 'decimal:1',
        'year_level' => 'integer',
        'enrolled_student' => 'integer', // ✅ ADDED
    ];

    /**
     * Get the program that owns the subject
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get faculty members who can teach this subject
     * (through program enrollment)
     */
    public function availableFaculty()
    {
        return $this->program->faculties();
    }

    /**
     * Scope to filter subjects by year level
     */
    public function scopeYear($query, $yearLevel)
    {
        return $query->where('year_level', $yearLevel);
    }

    /**
     * Scope to filter subjects by semester
     */
    public function scopeSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Get formatted semester display
     */
    public function getSemesterBadgeAttribute()
    {
        $colors = [
            '1st Semester' => 'blue',
            '2nd Semester' => 'green',
            'Summer' => 'yellow',
        ];

        return $colors[$this->semester] ?? 'gray';
    }
}

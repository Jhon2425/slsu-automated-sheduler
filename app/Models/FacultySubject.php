<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySubject extends Model
{
    use HasFactory;

    protected $table = 'faculty_subjects';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'faculty_id',
        'subject_id',
        'program_id',
        'lecture_units',
        'laboratory_units',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'lecture_units'     => 'decimal:1',
        'laboratory_units'  => 'decimal:1',
    ];

    /* =========================================================
     |  Relationships
     | ========================================================= */

    /**
     * Assigned faculty (User)
     */
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    /**
     * Subject being taught
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Program where subject is offered
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /* =========================================================
     |  Accessors
     | ========================================================= */

    /**
     * Total teaching units (lecture + laboratory)
     */
    public function getTotalUnitsAttribute(): float
    {
        return (float) ($this->lecture_units ?? 0)
             + (float) ($this->laboratory_units ?? 0);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'faculty';

    protected $fillable = [
        'user_id',
        'name',
        'civil_status',
        'birthdate',
        'employment_status',
        'home_address',
        'degree_earned',
        'year_graduated',
        'course',
        'school_graduated',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'year_graduated' => 'integer',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facultySubjects()
    {
        return $this->hasMany(FacultySubject::class, 'faculty_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'faculty_subject',
            'faculty_id',
            'subject_id'
        )
        ->withPivot('program_id', 'lecture_units', 'laboratory_units')
        ->withTimestamps();
    }

    /* ================= ACCESSORS ================= */

    public function getAgeAttribute()
    {
        return $this->birthdate?->age;
    }

    public function getFullDegreeAttribute()
    {
        return "{$this->degree_earned} in {$this->course}";
    }
}

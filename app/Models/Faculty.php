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
        'faculty_code',
        'user_id',
        'name',
        'civil_status',
        'birthdate',
        'employment_status',
        'home_address',
        'years_of_service',
        'rank',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'years_of_service' => 'integer',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educationalBackgrounds()
    {
        return $this->hasMany(EducationalBackground::class, 'faculty_id');
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
        ->withPivot('program_id', 'lecture_units', 'laboratory_units', 'year_level', 'semester', 'class_size')
        ->withTimestamps();
    }

    public function unavailabilities()
    {
        return $this->hasMany(FacultyUnavailability::class, 'faculty_id');
    }

    /* ================= ACCESSORS ================= */

    public function getAgeAttribute()
    {
        return $this->birthdate?->age;
    }

    public function getHighestDegreeAttribute()
    {
        $educations = $this->educationalBackgrounds;

        if ($educations->isEmpty()) {
            return null;
        }

        $degreeOrder = [
            'Doctorate Degree' => 4,
            'Master Degree' => 3,
            'Professional Degree' => 2,
            'Bachelor Degree' => 1,
        ];

        return $educations->sortByDesc(
            fn ($education) => $degreeOrder[$education->degree_earned] ?? 0
        )->first();
    }

    public function getFullDegreeAttribute()
    {
        $highestDegree = $this->highest_degree;

        if (!$highestDegree) {
            return 'No degree information';
        }

        return "{$highestDegree->degree_earned} in {$highestDegree->course}";
    }

    public function getPrimaryEducationAttribute()
    {
        return $this->educationalBackgrounds()
            ->orderByDesc('year_graduated')
            ->first();
    }
}
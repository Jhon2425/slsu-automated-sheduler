<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'program', 'faculty_id', 'faculty_code'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function enrollments()
    {
        return $this->hasMany(FacultyEnrollment::class, 'faculty_id');
    }

    public function programs()
    {
        return $this->belongsToMany(
            Program::class,
            'faculty_enrollments',
            'faculty_id',
            'program_id'
        )->withPivot(
            'enrollment_status', 'course_subject', 'year_section',
            'no_of_students', 'units', 'no_of_hours', 'action_type'
        )->withTimestamps();
    }

    public function createdProgram()
    {
        return $this->hasOne(Program::class, 'admin_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'faculty_subject', 'faculty_id', 'subject_id')
                    ->withPivot('program_id', 'lecture_units', 'laboratory_units')
                    ->withTimestamps();
    }

    public function facultySubjects()
    {
        return $this->hasMany(FacultySubject::class, 'faculty_id');
    }

    public function faculty()
    {
        return $this->hasOne(Faculty::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Returns the role name as a plain string.
     * Allows blade to do: auth()->user()->role === 'admin'
     * without breaking the belongsTo relationship internally.
     */
    public function getRoleAttribute(): string
    {
        return $this->getRelationValue('role')?->name
            ?? Role::find($this->role_id)?->name
            ?? '';
    }

    public function isAdmin(): bool
    {
        return $this->getRoleAttribute() === 'admin';
    }

    public function isFaculty(): bool
    {
        return $this->getRoleAttribute() === 'faculty';
    }
}
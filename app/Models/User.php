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
        'name', 'email', 'password', 'role_id', 'program', 'faculty_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->role->name === 'admin';
    }

    public function isFaculty()
    {
        return $this->role->name === 'faculty';
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

    /**
     * Get the subjects assigned to this faculty member.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'faculty_subjects', 'faculty_id', 'subject_id')
                    ->withTimestamps();
    }
}
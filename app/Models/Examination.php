<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examination extends Model
{
    use HasFactory;

    protected $table = 'examinations';

    protected $fillable = [
        'faculty_id',
        'subject_id',
        'classroom_id',
        'exam_date',
        'start_time',
        'end_time',
        'year_level',
        'semester',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships (optional but recommended)
    |--------------------------------------------------------------------------
    */

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}

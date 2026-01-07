<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'faculty_id',
        'classroom_id',
        'exam_date',
        'start_time',
        'end_time',
    ];

    // ================= RELATIONSHIPS =================

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}

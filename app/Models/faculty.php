<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'course_subject',
        'no_of_hours',
        'units',
        'no_of_students',
        'year_section',
        'action_type',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // Determine action type based on units
    public static function determineActionType($units, $actionType)
    {
        if ($actionType === 'Examination') {
            return 'Examination';
        }
        
        // Laboratory if units >= 3, Lecture otherwise
        return $units >= 3 ? 'Laboratory' : 'Lecture';
    }
}

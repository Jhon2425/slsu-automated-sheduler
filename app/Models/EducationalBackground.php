<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalBackground extends Model
{
    use HasFactory;

    protected $table = 'educational_backgrounds';

    protected $fillable = [
        'faculty_id',
        'degree_earned',
        'year_graduated',
        'course',
        'school_graduated',
    ];

    protected $casts = [
        'year_graduated' => 'integer',
    ];

    /**
     * Get the faculty that owns this educational background.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
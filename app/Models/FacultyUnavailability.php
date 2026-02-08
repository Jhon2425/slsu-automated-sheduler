<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyUnavailability extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'faculty_unavailabilities';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'faculty_id',
        'day',
        'time_from',
        'time_to',
        'reason',
    ];

    /**
     * Attribute casting
     * 
     * ⚠️ IMPORTANT: Do NOT cast time_from/time_to as datetime!
     * They are TIME fields in the database (e.g., "07:30:00")
     * Casting as datetime corrupts the values and prevents proper retrieval
     */
    protected $casts = [
        // Leave time fields as strings - they work fine that way
    ];

    /**
     * Days of the week (optional helper)
     */
    public const DAYS = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    /**
     * Relationship: unavailability belongs to a faculty
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Check if two time ranges overlap for a faculty on a given day
     */
    public static function hasOverlap(
        int $facultyId,
        string $day,
        string $timeFrom,
        string $timeTo,
        int $excludeId = null
    ): bool {
        $query = self::where('faculty_id', $facultyId)
            ->where('day', $day)
            ->where(function ($q) use ($timeFrom, $timeTo) {
                $q->whereBetween('time_from', [$timeFrom, $timeTo])
                  ->orWhereBetween('time_to', [$timeFrom, $timeTo])
                  ->orWhere(function ($q2) use ($timeFrom, $timeTo) {
                      $q2->where('time_from', '<=', $timeFrom)
                         ->where('time_to', '>=', $timeTo);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'capacity',
        'type',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}

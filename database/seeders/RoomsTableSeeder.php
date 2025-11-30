<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['room_name' => 'Room 101', 'capacity' => 40, 'type' => 'Lecture'],
            ['room_name' => 'Room 102', 'capacity' => 35, 'type' => 'Lecture'],
            ['room_name' => 'Room 103', 'capacity' => 45, 'type' => 'Lecture'],
            ['room_name' => 'Room 104', 'capacity' => 50, 'type' => 'Lecture'],
            ['room_name' => 'Room 105', 'capacity' => 30, 'type' => 'Lecture'],
            ['room_name' => 'Computer Lab 201', 'capacity' => 30, 'type' => 'Laboratory'],
            ['room_name' => 'Computer Lab 202', 'capacity' => 25, 'type' => 'Laboratory'],
            ['room_name' => 'Science Lab 301', 'capacity' => 35, 'type' => 'Laboratory'],
            ['room_name' => 'Physics Lab 302', 'capacity' => 28, 'type' => 'Laboratory'],
            ['room_name' => 'Chemistry Lab 303', 'capacity' => 30, 'type' => 'Laboratory'],
            ['room_name' => 'Multi-Purpose Room 401', 'capacity' => 50, 'type' => 'Both'],
            ['room_name' => 'Multi-Purpose Room 402', 'capacity' => 45, 'type' => 'Both'],
            ['room_name' => 'AVR Hall', 'capacity' => 60, 'type' => 'Both'],
            ['room_name' => 'Room 201', 'capacity' => 42, 'type' => 'Lecture'],
            ['room_name' => 'Room 202', 'capacity' => 38, 'type' => 'Lecture'],
            ['room_name' => 'Room 203', 'capacity' => 40, 'type' => 'Lecture'],
            ['room_name' => 'Examination Hall A', 'capacity' => 100, 'type' => 'Both'],
            ['room_name' => 'Examination Hall B', 'capacity' => 80, 'type' => 'Both'],
            ['room_name' => 'Engineering Lab 501', 'capacity' => 32, 'type' => 'Laboratory'],
            ['room_name' => 'Networking Lab 502', 'capacity' => 28, 'type' => 'Laboratory'],
            ['room_name' => 'Multimedia Lab 503', 'capacity' => 30, 'type' => 'Laboratory'],
        ];

        foreach ($rooms as $room) {
            $exists = DB::table('classrooms')->where('room_name', $room['room_name'])->first();
            if (!$exists) {
                DB::table('classrooms')->insert([
                    'room_name' => $room['room_name'],
                    'capacity' => $room['capacity'],
                    'type' => $room['type'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('Rooms seeded successfully!');
    }
}

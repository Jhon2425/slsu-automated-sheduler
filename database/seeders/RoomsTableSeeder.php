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
            ['room_name' => 'COMLAB 1', 'capacity' => 30, 'type' => 'Laboratory'],
            ['room_name' => 'COMLAB 2', 'capacity' => 30, 'type' => 'Laboratory'],
            ['room_name' => 'ROOM C203', 'capacity' => 40, 'type' => 'Lecture'],
            ['room_name' => 'ROOM C204', 'capacity' => 40, 'type' => 'Lecture'], 
            ['room_name' => 'DRILON HALL', 'capacity' => 40, 'type' => 'Lecture'],
        ];

        foreach ($rooms as $room) {
            $exists = DB::table('classrooms')
                ->where('room_name', $room['room_name'])
                ->first();

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

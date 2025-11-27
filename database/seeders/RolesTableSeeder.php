<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; // ← Must import this
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'faculty'];

        foreach ($roles as $roleName) {
            $exists = DB::table('roles')->where('name', $roleName)->first();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $roleName,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('Roles seeded successfully!');
    }
}

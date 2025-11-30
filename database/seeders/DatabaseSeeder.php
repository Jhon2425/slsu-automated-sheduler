<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            RoomsTableSeeder::class,
            // Add more seeders here as needed
        ]);

        $this->command->info('');
        $this->command->info('====================================');
        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('====================================');
        $this->command->info('');
    }
}
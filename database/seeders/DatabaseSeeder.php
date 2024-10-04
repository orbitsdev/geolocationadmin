<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\FileSeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\DeviceSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\AttendanceSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CouncilSeeder::class,
            PositionSeeder::class,
            EventSeeder::class,
            AttendanceSeeder::class,
            DeviceSeeder::class,
            FileSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

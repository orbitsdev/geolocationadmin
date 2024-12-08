<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Attendance;
use App\Models\CouncilPosition;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $event = Event::inRandomOrder()->first();
        // $councilPosition = CouncilPosition::inRandomOrder()->first();

        // // Sample attendance data
        // $attendances = [
        //     [
        //         'event_id' => $event->id ?? null,
        //         'council_position_id' => $councilPosition->id ?? null,
        //         'latitude' => 40.712776,
        //         'longitude' => -74.005974,
        //         'status' => 'present',
        //         'attendance_time' => now(),
        //         'check_in_time' => now(),
        //         'check_out_time' => now()->addHours(3),
        //         'attendance_code' => 'ATT123456',
        //         'device_id' => 'DEVICE001',
        //         'device_name' => 'iPhone 12',
        //         'selfie_image' => null,
        //         'attendance_allowed' => true,
        //         'notes' => 'Attended the full event.'
        //     ],
        //     [
        //         'event_id' => $event->id ?? null,
        //         'council_position_id' => $councilPosition->id ?? null,
        //         'latitude' => 34.052235,
        //         'longitude' => -118.243683,
        //         'status' => 'present',
        //         'attendance_time' => now(),
        //         'check_in_time' => now(),
        //         'check_out_time' => now()->addHours(5),
        //         'attendance_code' => 'ATT654321',
        //         'device_id' => 'DEVICE002',
        //         'device_name' => 'Samsung Galaxy S21',
        //         'selfie_image' => null,
        //         'attendance_allowed' => true,
        //         'notes' => 'Checked in and stayed for 5 hours.'
        //     ]
        // ];

        // // Insert attendance into the database
        // foreach ($attendances as $attendance) {
        //     Attendance::create($attendance);
        // }
    }
}

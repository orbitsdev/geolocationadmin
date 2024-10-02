<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['name' => 'Mayor'],
            ['name' => 'Vice Mayor'],
            ['name' => 'Secretary'],
            ['name' => 'Assistant Secretary'],
            ['name' => 'Treasurer'],
            ['name' => 'Assistant Treasurer'],
            ['name' => 'Auditor'],
            ['name' => 'Public Relations Officer'],
            ['name' => 'Communications Officer'],
            ['name' => 'Social Media Manager'],
            ['name' => 'Business Manager'],
            ['name' => 'Events Coordinator'],
            ['name' => 'Sports Coordinator'],
            ['name' => 'Academic Affairs Officer'],
            ['name' => 'Cultural Affairs Officer'],
            ['name' => 'Sergeant-at-Arms'],
            ['name' => 'Volunteer Coordinator'],
            ['name' => 'Membership Officer'],
            ['name' => 'IT Officer'],
            ['name' => 'Health & Safety Officer'],
            ['name' => 'Logistics Officer'],
            ['name' => 'Representative'],
            ['name' => 'Committee Chairperson']
        ];
        DB::table('positions')->insert($positions);
    }
}

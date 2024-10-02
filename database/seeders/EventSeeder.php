<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Council;
use App\Models\CouncilPosition;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $council = Council::inRandomOrder()->first();
        $councilPosition = CouncilPosition::inRandomOrder()->first();

        // Sample event data
        $events = [
            [
                'title' => 'Annual General Meeting',
                'description' => 'A meeting for all members to discuss the year ahead.',
                'content' => 'Detailed agenda of the meeting...',
                'latitude' => 40.712776,
                'longitude' => -74.005974,
                'radius' => 500,
                'start_time' => now()->addDays(1),
                'end_time' => now()->addDays(1)->addHours(3),
                'council_id' => $council->id ?? null,
                'council_position_id' => $councilPosition->id ?? null,
                'is_active' => true,
                'max_capacity' => 100,
                'type' => 'Meeting'
            ],
            [
                'title' => 'Charity Fundraiser',
                'description' => 'A fundraiser to support local charities.',
                'content' => 'Details about the fundraiser...',
                'latitude' => 34.052235,
                'longitude' => -118.243683,
                'radius' => 1000,
                'start_time' => now()->addWeeks(1),
                'end_time' => now()->addWeeks(1)->addHours(5),
                'council_id' => $council->id ?? null,
                'council_position_id' => $councilPosition->id ?? null,
                'is_active' => true,
                'max_capacity' => 200,
                'type' => 'Fundraiser'
            ]
        ];

        // Insert events into the database
        foreach ($events as $event) {
            Event::create($event);
        }
    }
}

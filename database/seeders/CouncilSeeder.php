<?php

namespace Database\Seeders;

use App\Models\Council;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CouncilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $councils = [
            // ['name' => '2020-2021', 'is_active' => false],
            // ['name' => '2021-2022', 'is_active' => false],
            // ['name' => '2022-2023', 'is_active' => false],  // Previously active
            ['name' => '2024-2025', 'is_active' => true],    // Currently active
        ];


        foreach ($councils as $councilData) {
            Council::create($councilData);
        }
    }
}

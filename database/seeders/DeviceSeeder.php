<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                // Seed devices for each user
                Device::create([
                    'user_id' => $user->id,
                    'device_token' => 'randomDeviceToken' . $user->id,
                    'device_id' => 'DEVICE' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                    'device_name' => 'Device Name ' . $user->id,
                    'device_type' => 'Android', // or 'iOS', 'Windows', etc.
                ]);

                Device::create([
                    'user_id' => $user->id,
                    'device_token' => 'randomDeviceToken' . $user->id . '2',
                    'device_id' => 'DEVICE' . str_pad($user->id + 1, 3, '0', STR_PAD_LEFT),
                    'device_name' => 'Device Name ' . ($user->id + 1),
                    'device_type' => 'iOS',
                ]);
            }
        }
    }
}

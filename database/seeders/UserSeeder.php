<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'geo@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        User::create([
            'first_name' => 'User1',
            'last_name' => 'Account',
            'email' => 'u1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        User::create([
            'first_name' => 'User2',
            'last_name' => 'User 2',
            'email' => 'u2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}

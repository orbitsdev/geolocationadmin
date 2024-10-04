<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        File::create([
            'fileable_id' => 1, // Assuming this is linked to a Task, Post, etc.
            'fileable_type' => 'App\Models\Task', // Example type, you can change this as per your models
            'file' => 'uploads/sample-file.pdf',
            'file_name' => 'sample-file.pdf',
            'file_type' => 'application/pdf',
            'file_size' => '1024', // Example file size in bytes
        ]);

        File::create([
            'fileable_id' => 2,
            'fileable_type' => 'App\Models\Post', // Example type
            'file' => 'uploads/image.png',
            'file_name' => 'image.png',
            'file_type' => 'image/png',
            'file_size' => '2048',
        ]);
    }
}

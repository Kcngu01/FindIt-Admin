<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // List of faculties from the image
        $faculties = [
            'Faculty of Economics and Business',
            'Faculty of Engineering',
            'Faculty of Applied and Creative Arts',
            'Faculty of Cognitive Sciences and Human Development',
            'Faculty of Medicine and Health Sciences',
            'Faculty of Social Sciences and Humanities',
            'Faculty of Resource Science and Technology',
            'Faculty of Computer Science and Information Technology',
            'Faculty of Language and Communication',
            'Faculty of Built Environment',
        ];

        // Insert each faculty into the database
        foreach ($faculties as $facultyName) {
            Faculty::create([
                'name' => $facultyName,
            ]);
        }
    }
} 
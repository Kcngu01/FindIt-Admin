<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Student::factory()->count(10)->create();
        Student::factory()->create([
            'name' => 'Student 1',
            'email' => 'student1@example.com',
            'password' => 'student123',
        ]);
    }
}

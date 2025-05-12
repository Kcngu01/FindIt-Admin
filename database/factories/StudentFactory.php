<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'matric_no' => fake()->unique()->numberBetween(100000, 999999),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // default password
            'email_verified_at' => now(),
        ];
    }
}

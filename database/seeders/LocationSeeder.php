<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Location::create(['id' => 1, 'name' => 'CTF1']);
        Location::create(['id' => 2, 'name' => 'CTF2']);
        Location::create(['id' => 3, 'name' => 'CTF3']);
        Location::create(['id' => 4, 'name' => 'CTF4']);
        Location::create(['id' => 5, 'name' => 'HEPA']);
        Location::create(['id' => 6, 'name' => 'DETAR PUTRA']);
        Location::create(['id' => 7, 'name' => 'Faculty of Economics and Business']);
        Location::create(['id' => 8, 'name' => 'Faculty of Engineering']);
        Location::create(['id' => 9, 'name' => 'Faculty of Applied and Creative Arts']);
        Location::create(['id' => 10, 'name' => 'Faculty of Cognitive Sciences and Human Development']);
        Location::create(['id' => 11, 'name' => 'Faculty of Medicine and Health Sciences']);
        Location::create(['id' => 12, 'name' => 'Faculty of Social Sciences and Humanities']);
        Location::create(['id' => 13, 'name' => 'Faculty of Resource Science and Technology']);
        Location::create(['id' => 14, 'name' => 'Faculty of Computer Science and Information Technology']);
        Location::create(['id' => 15, 'name' => 'Faculty of Language and Communication']);
        Location::create(['id' => 16, 'name' => 'Faculty of Built Environment']);
        Location::create(['id' => 17, 'name' => 'Central UNIMAS Building for Educators (CUBE)']);
        Location::create(['id' => 18, 'name' => 'UNIMAS Student Pavilion']);
        Location::create(['id' => 19, 'name' => 'Library (CAIS)']);
        Location::create(['id' => 20, 'name' => 'Sports Centre']);
        Location::create(['id' => 21, 'name' => 'Bus Stops']);
        Location::create(['id' => 22, 'name' => 'Others']);
    }
}

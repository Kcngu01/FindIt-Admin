<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Colour;

class ColourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Colour::create(['id' => 1, 'name' => 'Black']);
        Colour::create(['id' => 2, 'name' => 'Blue']);
        Colour::create(['id' => 3, 'name' => 'White']);
        Colour::create(['id' => 4, 'name' => 'Gray']);
        Colour::create(['id' => 5, 'name' => 'Silver']);
        Colour::create(['id' => 6, 'name' => 'Gold']);
        Colour::create(['id' => 7, 'name' => 'Brown']);
        Colour::create(['id' => 8, 'name' => 'Beige']);
        Colour::create(['id' => 9, 'name' => 'Red']);
        Colour::create(['id' => 10, 'name' => 'Green']);
        Colour::create(['id' => 11, 'name' => 'Yellow']);
        Colour::create(['id' => 12, 'name' => 'Orange']);
        Colour::create(['id' => 13, 'name' => 'Pink']);
        Colour::create(['id' => 14, 'name' => 'Purple']);
        Colour::create(['id' => 15, 'name' => 'Multicolor/Patterned']);
        Colour::create(['id' => 16, 'name' => 'Others']);
    }
}

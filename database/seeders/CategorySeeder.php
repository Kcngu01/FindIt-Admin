<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Category::create(['id' => 1, 'name' => 'Laptop']);
        Category::create(['id' => 2, 'name' => 'Smartphone']);
        Category::create(['id' => 3, 'name' => 'Headphone']);
        Category::create(['id' => 4, 'name' => 'Charger']);
        Category::create(['id' => 5, 'name' => 'Tablet']);
        Category::create(['id' => 6, 'name' => 'Power Bank']);
        Category::create(['id' => 7, 'name' => 'Calculator']);
        Category::create(['id' => 8, 'name' => 'Watch']);
        Category::create(['id' => 9, 'name' => 'External Hard Drive']);
        Category::create(['id' => 10, 'name' => 'Camera']);
        Category::create(['id' => 11, 'name' => 'Book']);
        Category::create(['id' => 12, 'name' => 'ID Card']);
        Category::create(['id' => 13, 'name' => 'Bag']);
        Category::create(['id' => 14, 'name' => 'Pen/Pencil/Markers/Highlighters']);
        Category::create(['id' => 15, 'name' => 'Water Bottle']);
        Category::create(['id' => 16, 'name' => 'Clothes/Jacket/Hoodie']);
        Category::create(['id' => 17, 'name' => 'Hat/Cap']);
        Category::create(['id' => 18, 'name' => 'Glasses']);
        Category::create(['id' => 19, 'name' => 'Jewelry']);
        Category::create(['id' => 20, 'name' => 'Umbrella']);
        Category::create(['id' => 21, 'name' => 'Shoes']);
        Category::create(['id' => 22, 'name' => 'Wallet']);
        Category::create(['id' => 23, 'name' => 'Keys']);
        Category::create(['id' => 24, 'name' => 'Money/Cash/Credit/Debit Card']);
        Category::create(['id' => 25, 'name' => 'Others']);
    }
}

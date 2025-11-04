<?php

namespace Database\Seeders;

use App\Models\Product\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $parentCategories = Category::factory()->count(5)->create([
            'parent_id' => null,
        ]);

        Category::factory()->count(15)->create([
            'parent_id' => $parentCategories->random()->id,
        ]);
    }
}

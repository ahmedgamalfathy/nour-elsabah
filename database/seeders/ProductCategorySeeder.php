<?php

namespace Database\Seeders;

use App\Models\Product\Product;
use Illuminate\Database\Seeder;
use App\Models\Product\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        // 3 products with NO category
        Product::factory()->count(3)->create([
            'category_id' => null,
            'sub_category_id' => null,
        ]);

        // 3 products with ONLY category_id
        Product::factory()->count(5)->create()->each(function ($product) use ($categories) {
            $product->update([
                'category_id' => $categories->random()->id,
                'sub_category_id' => null,
            ]);
        });

        // 4 products with category_id AND sub_category_id (different)
        Product::factory()->count(4)->create()->each(function ($product) use ($categories) {
            $main = $categories->random();
            $sub = $categories->where('id', '!=', $main->id)->random();

            $product->update([
                'category_id' => $main->id,
                'sub_category_id' => $sub->id,
            ]);
        });
    }
}

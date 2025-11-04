<?php

namespace Database\Seeders;

use App\Models\Product\ProductMedia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductMedia::factory()->count(20)->create();
    }
}

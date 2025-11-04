<?php

namespace Database\Factories\Product;

use App\Enums\IsMain;
use App\Enums\Media\MediaType;
use App\Models\Product\Product;
use App\Models\Product\ProductMedia;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProductMediaFactory extends Factory
{
    protected $model =ProductMedia::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => 'ProductMedia/watch2.jpg',
            'type' => $this->faker->randomElement([MediaType::IMAGE->value, MediaType::VIDEO->value]),
            'is_main' => $this->faker->randomElement([IsMain::PRIMARY->value, IsMain::SECONDARY->value]),
            'product_id' => Product::inRandomOrder()->first()->id,
        ];
    }
}

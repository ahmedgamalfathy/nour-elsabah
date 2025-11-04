<?php

namespace Database\Factories\Product;

use App\Models\Product\Category;
use Illuminate\Database\Eloquent\Factories\Factory;


class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Category::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'parent_id' => $this->faker->numberBetween(1, 4),
            'is_active' => $this->faker->boolean(),
            'path' => 'ProductMedia/index.jpg'
        ];
    }
}

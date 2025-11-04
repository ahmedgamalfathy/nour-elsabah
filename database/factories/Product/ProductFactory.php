<?php

namespace Database\Factories\Product;

use App\Enums\Product\LimitedQuantity;
use App\Enums\Product\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product\Product>
 */
class ProductFactory extends Factory
{
    protected $model = \App\Models\Product\Product::class;
    /**
     * Define the model's default state.
     *
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a random price
        $price = $this->faker->randomFloat(2, 50, 1000);

        // Generate a random cost that is less than the price
        $cost = $this->faker->randomFloat(2, 0, $price);

        // Determine if the product is limited or unlimited
        $isLimitedQuantity = $this->faker->randomElement([LimitedQuantity::LIMITED->value, LimitedQuantity::UNLIMITED->value]);

        // Set quantity based on the isLimitedQuantity value
        $quantity = $isLimitedQuantity === LimitedQuantity::LIMITED->value ? $this->faker->numberBetween(1, 100) : 0;

        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence,
            'cost' => $cost,
            'is_limited_quantity' => $isLimitedQuantity,
            'quantity' => $quantity,
            'price' => $price,
            'status' => $this->faker->randomElement([ProductStatus::ACTIVE->value, ProductStatus::INACTIVE->value]),
            
        ];
    }
}

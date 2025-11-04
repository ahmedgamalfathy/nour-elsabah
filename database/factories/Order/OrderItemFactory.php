<?php

namespace Database\Factories\Order;

use App\Models\Order\Order;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    // Fetch a random existing order or create one if none exists
        $order = Order::inRandomOrder()->first() ?? Order::factory()->create();

        // Fetch a random existing product or create one if none exists
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();

        return [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => $product->price,
            'cost' => $product->cost,
            'qty' => $this->faker->numberBetween(1, 10),
        ];
    }
}

<?php

namespace Database\Factories\Order;

use App\Models\Order\Order;
use App\Models\Client\Client;
use App\Enums\Order\DiscountType;
use App\Models\Client\ClientEmail;
use App\Models\Client\ClientPhone;
use Illuminate\Support\Facades\DB;
use App\Models\Client\ClientAdrress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
// resolve order and order item
//seeder and factory

    public function definition(): array
    {
        $client = Client::factory()->create();
        $clientPhone = ClientPhone::factory()->create(['client_id' => $client->id]);
        $clientEmail = ClientEmail::factory()->create(['client_id' => $client->id]);
        $clientAddress = ClientAdrress::factory()->create(['client_id' => $client->id]);


        return [
            'number' => 'ORD_' . rand(1000, 9999) . date('m') . date('y'),
            'client_id' => $client->id,
            'client_phone_id' => $clientPhone->id,
            'client_email_id' => $clientEmail->id,
            'client_address_id' => $clientAddress   ->id,
            'status' => $this->faker->boolean(),
            'discount_type' => DiscountType::NO_DISCOUNT,
            'discount' => 0,
            'price' => 0,
            'price_after_discount' => 0,
            'total_cost' => 0,
        ];
    }
}

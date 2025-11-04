<?php

namespace Database\Factories\Client;

use App\Models\Client\Client;
use App\Models\Client\ClientPhone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client\ClientPhone>
 */
class ClientPhoneFactory extends Factory
{
    protected $model =ClientPhone::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => $this->faker->phoneNumber(),
            'is_main' => $this->faker->boolean(),
            'country_code' => $this->faker->countryCode(),
            'client_id'=> Client::factory()
        ];
    }
}

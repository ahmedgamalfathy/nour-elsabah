<?php

namespace Database\Factories\Client;


use App\Models\Client\Client;
use App\Models\Client\ClientEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client\ClientEmail>
 */
class ClientEmailFactory extends Factory
{

    //name, notes
    //client_id , address, is_main
    //client_id , phone , is_main , country_code
    protected $model = ClientEmail::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    { //client_id , email, is_main
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'is_main' => $this->faker->boolean(),
            'client_id'=>Client::factory()
        ];
    }
}

<?php

namespace Database\Factories\Client;

use App\Models\Client\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client\ClientAdrress;

class ClientAdrressFactory extends Factory
{
    protected $model = ClientAdrress::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {    //client_id , address, is_main
        return [
            'address' => $this->faker->address(),
            'is_main' => $this->faker->boolean(),
            'client_id'=> Client::factory(),
            'street_number'=>$this->faker->streetAddress(),
            'city' =>$this->faker->city(),
            'region'=>$this->faker->city()

        ];
    }
}

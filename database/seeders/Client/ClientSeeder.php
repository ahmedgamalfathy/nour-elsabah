<?php

namespace Database\Seeders\Client;

use App\Models\Client\Client;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::factory()->count(10)->create()->each(function ($client) {
            $client->addresses()->saveMany(\App\Models\Client\ClientAdrress::factory()->count(3)->make());
            $client->emails()->saveMany(\App\Models\Client\ClientEmail::factory()->count(3)->make());
            $client->phones()->saveMany(\App\Models\Client\ClientPhone::factory()->count(3)->make());
        });
    }
}

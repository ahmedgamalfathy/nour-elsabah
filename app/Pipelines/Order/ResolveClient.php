<?php

namespace App\Pipelines\Order;

use App\DTOs\Order\OrderCheckoutData;
use App\Enums\IsMain;
use App\Models\Client\Client;
use App\Models\Client\ClientAdrress;
use App\Models\Client\ClientEmail;
use App\Models\Client\ClientPhone;
use Closure;

/**
 * Resolves the checkout client for both auth and guest flows.
 *
 * Authenticated checkouts already have a client id, so the stage simply
 * verifies the client exists. Guest checkouts create the client aggregate root
 * and its primary contact records once, avoiding duplicated controller logic.
 */
class ResolveClient
{
    public function handle(OrderCheckoutData $data, Closure $next): mixed
    {
        if ($data->clientId !== null) {
            Client::findOrFail($data->clientId);

            return $next($data);
        }

        $client = Client::create([
            'name' => $data->inputData['name'],
            'note' => $data->inputData['note'] ?? null,
        ]);

        $phone = ClientPhone::create([
            'client_id' => $client->id,
            'phone' => $data->inputData['phone'],
            'country_code' => $data->inputData['countryCode'] ?? null,
            'is_main' => IsMain::PRIMARY->value,
        ]);

        $email = ClientEmail::create([
            'client_id' => $client->id,
            'email' => $data->inputData['email'],
            'is_main' => IsMain::PRIMARY->value,
        ]);

        $address = ClientAdrress::create([
            'client_id' => $client->id,
            'address' => $data->inputData['address'],
            'street_number' => $data->inputData['streetNumber'] ?? null,
            'city' => $data->inputData['city'] ?? null,
            'region' => $data->inputData['region'] ?? null,
            'is_main' => IsMain::PRIMARY->value,
        ]);

        $data->clientId = $client->id;
        $data->clientPhoneId = $phone->id;
        $data->clientEmailId = $email->id;
        $data->clientAddressId = $address->id;

        return $next($data);
    }
}

<?php

namespace App\Http\Resources\Client\ClientContact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllClientContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'clientPhoneId' => $this->id,
            'clientId' => $this->client_id,
            'phone' => $this->phone,
            'isMain' => $this->is_main,
            'countryCode' => $this->country_code,
        ];
    }
}

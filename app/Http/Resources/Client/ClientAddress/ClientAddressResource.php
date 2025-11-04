<?php

namespace App\Http\Resources\Client\ClientAddress;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //dd($this->countries->toArray());
        return [
            'clientAddressId' => $this->id,
            'clientId' => $this->client_id,
            'address' => $this->address,
            'isMain' => $this->is_main,
            'streetNumber'=>$this->street_number??"",
            'city' =>$this->city??"",
            'region'=>$this->region??""
        ];

    }
}

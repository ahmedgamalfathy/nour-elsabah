<?php

namespace App\Http\Resources\Client\website;

use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Enums\Order\OrderStatus;
use App\Enums\Client\ClientStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class AllProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
                'clientUserId' => $this->id,
                'clientId' => $this->client_id,
                'name' => $this->name,
                'avatar' => $this->avatar,
                'email' => $this->email,
                "orderIdInCart" => Client::where('id',$this->client_id)->first()->orders()->where('status',OrderStatus::IN_CART)->first()->id??"",
        ];
    }
}

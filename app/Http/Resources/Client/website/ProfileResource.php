<?php

namespace App\Http\Resources\Client\website;

use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Order\Website\OrderResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
                'products'=> OrderResource::collection($this->orders),
        ];
    }
}

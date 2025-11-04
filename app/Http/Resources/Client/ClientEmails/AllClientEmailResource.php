<?php

namespace App\Http\Resources\Client\ClientEmails;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllClientEmailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


        return [
            'clientEmailId' => $this->id,
            'clientId' => $this->client_id,
            'email' => $this->email,
            'isMain' => $this->is_main,
        ];
    }
}

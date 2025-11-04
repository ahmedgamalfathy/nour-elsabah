<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Client\ClientEmails\AllClientEmailResource;
use App\Http\Resources\Client\ClientAddress\AllClientAddressResource;
use App\Http\Resources\Client\ClientContact\AllClientContactResource;

class AllClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//name ,notes
        return [
            'clientId' => $this->id,
            'name' => $this->name,
            'address' => $this->addresses ? ($this->addresses->where('is_main', 1)->first()?new AllClientAddressResource($this->addresses->where('is_main', 1)->first()):new AllClientAddressResource($this->addresses->first())) : null,
            'phone' =>$this->phones?($this->phones->where('is_main', 1)->first()?new AllClientContactResource($this->phones->where('is_main', 1)->first()):new AllClientContactResource($this->phones->first())) :null,
            'email'=> $this->emails?($this->emails->where('is_main', 1)->first()?new AllClientEmailResource($this->emails->where('is_main', 1)->first()):new AllClientEmailResource($this->emails->first())) :null,
        ];
    }
}

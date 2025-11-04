<?php
namespace App\Http\Resources\Client;
use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;
class LoggedInClientResource extends JsonResource{
 /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "clientId"=>$this->client_id??"",
            "avatar"=>$this->avatar,
            "name" => $this->name,
            "email" => $this->email,
        ];
    }
}

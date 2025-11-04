<?php

namespace App\Http\Resources\ProductMedia\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductMediaResouce extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'productMediaId' => $this->id,
            'path' => $this->path,
        ];
    }
}

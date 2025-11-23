<?php

namespace App\Http\Resources\SubSlider;

use App\Http\Resources\SubSlider\SubSliderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllSubSliderResource extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => SubSliderResource::collection($this->collection),
            'meta' => [
                'next_cursor' => $this->resource->nextCursor()?->encode(),
                'prev_cursor' => $this->resource->previousCursor()?->encode(),
                'per_page' => $this->resource->perPage(),
            ],
        ];
    }
}

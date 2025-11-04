<?php

namespace App\Http\Resources\Slider;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Product\SliderProductResource;


class SliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sliderId' => $this->id,
            'name' => $this->name,
            'productsCount' => $this->products()->where('status', 1)->count(),
            // 'startDate' => $this->start_date,
            // 'endDate' => $this->end_date,
            'isActive' => $this->is_active,
            'createdAt' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'sliderItems'=> SliderProductResource::collection($this->products()->where('status', 1)->get())
        ];
    }
}

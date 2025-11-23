<?php

namespace App\Http\Resources\SubSlider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SubSliderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ,
            'title' => $this->title,
            'images' => $this->getImageUrls(),
            'is_active' => $this->is_active,
            // 'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get full URLs for images
     */
    private function getImageUrls(): array
    {
        if (!$this->images || !is_array($this->images)) {
            return [];
        }

        return array_map(function ($image) {
            return Storage::disk('public')->url($image);
        }, $this->images);
    }
}

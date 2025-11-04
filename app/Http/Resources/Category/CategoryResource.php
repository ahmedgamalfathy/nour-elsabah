<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Category\SubCategory\SubCategoryResource;


class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'categoryId' => $this->id,
            'name' => $this->name,
            'isActive' => $this->is_active,
            'path' => $this->path,
            'subCategories' => SubCategoryResource::collection($this->whenLoaded("subCategories")),
        ];
    }
}

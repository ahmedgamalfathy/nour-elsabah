<?php

namespace App\Http\Resources\Category\SubCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'subCategoryId' => $this->id,
            'subCategoryName' => $this->name,
            'subCategoryIsActive' => $this->is_active,
            'subCategoryPath' => $this->path,
        ];
    }
}

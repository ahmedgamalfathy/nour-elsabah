<?php

namespace App\Http\Resources\Category\CategoryTwo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            // 'parentId'=>$this->parent_id??"",
            'productsCount' => DB::table('products')->where('category_id', $this->id)->
            orWhere('sub_category_id',$this->id)->count(),
            // 'subCategoriesCount'=>$this->subCategories()->count(),
            // 'subCategories' => SubCategoryResource::collection($this->subCategories),
        ];
    }
}

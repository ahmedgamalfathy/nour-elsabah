<?php

namespace App\Http\Resources\Category\CategoryTwo;

use App\Http\Resources\Category\SubCategory\SubCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AllCategoryResource extends JsonResource
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
            'productCount' => DB::table('products')->where('category_id', $this->id)->
            orWhere('sub_category_id',$this->id)->count(),
            // 'subCategoriesCount'=>$this->subCategories()->count(),
            // 'subCategories' => SubCategoryResource::collection($this->subCategories->take(10)),
        ];
    }
}

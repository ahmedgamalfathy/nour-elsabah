<?php

namespace App\Http\Resources\Product\Website;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Product\Website\AllProductResource;
use App\Http\Resources\ProductMedia\ProductMediaResouce;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'productId' => $this->id,
            'name' => $this->name,
            'price' => number_format($this->price, 2, '.', ''),
            'status' => $this->status,
            'description' => $this->description??"",
            "categoryId" => $this->category_id??"",
            "subCategoryId"=> $this->sub_category_id??"",
            "specifications"=> $this->specifications??"",
            "stock"=> ($this->quantity <= 0 || $this->quantity < 10) ? ($this->quantity <= 0 ? "" : $this->quantity) : "",
           'productMedia' =>$this->productMedia->isNotEmpty()? ProductMediaResouce::collection($this->productMedia): url('storage/ProductMedia/default-product.jpg'),
           "similarProducts" => AllProductResource::collection($this->getSimilarProduct())
        ];//
    }
}

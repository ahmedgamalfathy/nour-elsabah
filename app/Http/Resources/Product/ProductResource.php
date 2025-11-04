<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryResource;
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
            'price' => $this->price,
            'status' => $this->status,
            'cost' => $this->cost??"",
            'qty'=>$this->quantity??"",
            'isLimitedQuantity'=>$this->is_limited_quantity,
            'description' => $this->description??"",
            "categoryId" => $this->category_id??"",
            "subCategoryId"=> $this->sub_category_id??"",
            "specifications"=>$this->specifications??"",
            'crossedPrice'=>$this->crossed_price??"",
            'isFreeShipping'=>$this->is_free_shipping,
            'unitType'=>$this->unit_type,
           'productMedia' =>$this->productMedia->isNotEmpty()? ProductMediaResouce::collection($this->productMedia) :url("storage/".'ProductMedia/default-product.jpg') ,

        ];
    }
}

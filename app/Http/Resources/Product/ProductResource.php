<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\ProductMedia\ProductMediaResouce;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'productId'          => $this->id,
            'name'               => $this->name,
            'price'              => $this->price,
            'status'             => $this->status,
            'cost'               => $this->cost ?? '',
            'qty'                => $this->quantity ?? '',
            'minQuantity'        => $this->min_quantity??'',
            'quantityStep'       => $this->quantity_step??'',
            'unitId'             =>$this->unit_id??'',
            'unit'=>[
                "id"=>$this->unit->id?? "",
                "name"=>$this->unit->name?? "",
                "value"=>$this->unit->step?? "",
            ],
            'isLimitedQuantity'  => $this->is_limited_quantity,
            'description'        => $this->description ?? '',
            'categoryId'         => $this->category_id ?? '',
            'subCategoryId'      => $this->sub_category_id ?? '',
            'specifications'     => $this->specifications ?? '',
            'crossedPrice'       => $this->crossed_price ?? '',
            'isFreeShipping'     => $this->is_free_shipping,
            'unitType'           => $this->unit_type,
            'productMedia'       => $this->productMedia->isNotEmpty()
                ? ProductMediaResouce::collection($this->productMedia)
                : url('storage/ProductMedia/default-product.jpg'),
        ];
    }
}

<?php

namespace App\Http\Resources\Order\OrderItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductMedia\ProductMediaResouce;


class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'orderItemId' => $this->id,
            'orderId' => $this->order_id,
            'price' => $this->price,
            'qty' => $this->qty,
            'cost'=>$this->cost,
            'product' => [
                'productId' => $this->product_id,
                'name' => $this->product->name,
                'quantity' => $this->product->quantity,
                'isLimitedQuantity' => $this->product->is_limited_quantity,
                'path'=>
                $this->product->firstProductMedia
                ? new ProductMediaResouce($this->product->firstProductMedia)
                : ($this->product->productMedia->isNotEmpty()
                ? ProductMediaResouce::collection($this->product->productMedia->take(1))
                : url('storage/ProductMedia/default-product.jpg')),
            ]
        ];

    }
}

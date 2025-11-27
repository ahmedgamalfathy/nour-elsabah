<?php

namespace App\Http\Resources\Order\OrderItem\Website;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductMedia\Website\ProductMediaResouce;



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
            'orderId' => $this->order_id,
            'orderItemId' => $this->id,
            'price' => $this->price,
            'qty' => $this->qty,
            // 'cost'=>$this->cost,
            'product' => [
                'productId' => $this->product_id,
                'name' => $this->product->name,
                'isShippingFree' => $this->product->is_shipping_free??0,
                'crossedPrice' => $this->product->crossed_price,
                // 'path'=> ProductMediaResouce::collection($this->product->productMedia) ,
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

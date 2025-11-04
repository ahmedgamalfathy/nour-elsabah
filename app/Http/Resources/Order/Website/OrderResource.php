<?php

namespace App\Http\Resources\Order\Website;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Order\OrderItem\OrderItemResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'orderId' => $this->id,
            'orderNumber' => $this->number,
            'status' => $this->status,
            'price' => $this->price,
            'priceAfterDiscount' => $this->price_after_discount,
            'products' =>count($this->items),
            'date' =>Carbon::parse($this->created_at)->format('Y-m-d')
            // 'orderItems'=> OrderItemResource::collection($this->items),
        ];
    }
}

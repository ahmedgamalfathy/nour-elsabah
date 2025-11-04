<?php

namespace App\Http\Resources\Order;

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
            'clientId' => $this->client_id,
            'clientPhoneId' => $this->client_phone_id??'',
            'clientEmailId' => $this->client_email_id??'',
            'clientAddressId' => $this->client_address_id??'',
            'status' => $this->status,
            'discountType' => $this->discount_type,
            'discount' => $this->discount,
            'price' => $this->price,
            'totalOrderCost'=>$this->total_cost,
            'priceAfterDiscount' => $this->price_after_discount,
            'date' =>Carbon::parse($this->created_at)->format('d/m/Y'),
            'totalOrderItems'=>$this->items->count(),
            'orderItems'=> OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

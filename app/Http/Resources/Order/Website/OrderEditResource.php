<?php

namespace App\Http\Resources\Order\Website;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Order\OrderItem\Website\OrderItemResource;


class OrderEditResource extends JsonResource
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
            'number' => $this->number ?? "",
            'phone' => $this->clientPhone->phone ?? "",
            'address' => $this->clientAddress->address ?? "",
            'email' => $this->clientEmail->email ?? "",
            'status' => $this->status ?? "",
            'price' => $this->price ?? "",
            'discount' => $this->discount ?? "",
            // 'pointsEarned' => $this->points_earned,
            // 'pointsRedeemed' => $this->points_redeemed,
            'pointsDiscountAmount' => $this->points_discount_amount,
            'priceAfterDiscount' => $this->price_after_discount ?? "",
            'date' => Carbon::parse($this->created_at)->format('d/m/Y'),
            'items' => OrderItemResource::collection($this->items), // items هنا
        ];
    }
}

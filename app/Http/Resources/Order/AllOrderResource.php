<?php

namespace App\Http\Resources\Order;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class AllOrderResource  extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {//"orderId ,orderNumber ,clientName ,status ,price totalOrderCost priceAfterDiscount totalOrderItems date"
        return [
            'orderId' => $this->id,
            'orderNumber' => $this->number,
            'clientName' => $this->client?->name??'',
            'status' => $this->status,
            'discountType' => $this->discount_type,
            'discount' => $this->discount,
            'price' => $this->price,
            'totalOrderCost'=>$this->total_cost,
            'priceAfterDiscount' => $this->price_after_discount,
            'totalOrderItems'=>$this->items->count(),
            'date' =>Carbon::parse($this->created_at)->format('d/m/Y')
        ];
    }
}

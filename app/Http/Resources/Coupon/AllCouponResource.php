<?php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class AllCouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'couponId' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'minOrderAmount' => $this->min_order_amount,
            'maxDiscount' => $this->max_discount,
            'usageLimit' => $this->usage_limit,
            'usedCount' => $this->used_count,
            'perUserLimit' => $this->per_user_limit,
            'startsAt' => Carbon::parse($this->starts_at)->format('d/m/Y'),
            'expiresAt' => Carbon::parse($this->expires_at)->format('d/m/Y'),
            'isActive' => $this->is_active,
        ];
    }
}

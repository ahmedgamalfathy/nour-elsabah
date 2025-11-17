<?php

namespace App\Models\Coupon;

use Illuminate\Database\Eloquent\Model;
use App\Models\Coupon\CouponUsage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order\Order;
class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function usage(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Check if coupon is valid
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    // Check if user can use this coupon
    public function canBeUsedByClient($clientId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $userUsageCount = $this->usage()->where('client_id', $clientId)->count();

        return $userUsageCount < $this->per_user_limit;
    }

    // Calculate discount amount
    public function calculateDiscount($orderAmount): float
    {
        if ($orderAmount < $this->min_order_amount) {
            return 0;
        }

        if ($this->type === 'percentage') {
            $discount = $orderAmount * ($this->value / 100);

            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }

            return round($discount, 2);
        }

        // Fixed discount
        return min($this->value, $orderAmount);
    }
}

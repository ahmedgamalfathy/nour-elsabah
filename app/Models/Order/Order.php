<?php

namespace App\Models\Order;

use App\Models\Client\Client;
use App\Models\Order\OrderItem;
use App\Enums\Order\OrderStatus;
use App\Traits\CreatedUpdatedBy;
use App\Enums\Order\DiscountType;
use App\Models\Client\ClientEmail;
use App\Models\Client\ClientPhone;
use App\Models\Client\ClientAdrress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Order extends Model
{
    use HasFactory;
    protected $guarded = []; 

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'discount_type' => DiscountType::class,
            'inventory_deducted' => 'boolean',
            'discount' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'price' => 'decimal:2',
            'price_after_discount' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'points_discount_amount' => 'decimal:2',
        ];
    }


    public static function boot()
    {
        parent::boot();
        static::creating(function($model){
            $model->number = 'ORD'.'_'.rand(1000,9999).date('m' ).date('y');
        });
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function clientPhone()
    {
        return $this->belongsTo(ClientPhone::class);
    }
    public function clientEmail()
    {
        return $this->belongsTo(ClientEmail::class);
    }
    public function clientAddress()
    {
        return $this->belongsTo(ClientAdrress::class);
    }

    /**
     * Recalculate the order monetary state from persisted line items.
     *
     * This method is the aggregate boundary for pricing. Controllers and
     * application services may decide which items exist, but the Order model is
     * the single source of truth for totals, order-level discounts, coupon
     * discounts, and redeemed-points discounts.
     */
    public function recalculateTotals(): self
    {
        $items = $this->items()->get(['price', 'cost', 'qty']);

        $price = $items->reduce(
            fn (float $carry, OrderItem $item): float => $carry + ((float) $item->price * (float) $item->qty),
            0.0
        );

        $cost = $items->reduce(
            fn (float $carry, OrderItem $item): float => $carry + ((float) $item->cost * (float) $item->qty),
            0.0
        );

        $orderDiscount = $this->calculateOrderDiscount($price);
        $couponDiscount = (float) ($this->coupon_discount ?? 0);
        $pointsDiscount = (float) ($this->points_discount_amount ?? 0);

        $this->forceFill([
            'price' => $price,
            'total_cost' => $cost,
            'price_after_discount' => max(0, $price - $orderDiscount - $couponDiscount - $pointsDiscount),
        ])->saveQuietly();

        return $this->refresh();
    }

    public function isLockedForCheckout(): bool
    {
        return $this->status === OrderStatus::CHECKOUT;
    }

    private function calculateOrderDiscount(float $price): float
    {
        $discount = (float) ($this->discount ?? 0);

        return match ($this->discount_type) {
            DiscountType::PERCENTAGE => $price * ($discount / 100),
            DiscountType::FIXCED => $discount,
            default => 0.0,
        };
    }
}

<?php

namespace App\Models\Order;

use App\Models\Product\Product;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use  HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'cost',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'qty' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (OrderItem $item): void {
            $item->order?->recalculateTotals();
        });

        static::deleted(function (OrderItem $item): void {
            $item->order?->recalculateTotals();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

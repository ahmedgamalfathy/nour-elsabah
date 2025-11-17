<?php

namespace App\Models\Coupon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Coupon\Coupon;
use App\Models\Client\Client;
use App\Models\Order\Order;
class CouponUsage extends Model
{
    protected $table = 'coupon_usage';

    public $timestamps = false;

    protected $fillable = [
        'coupon_id',
        'client_id',
        'order_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}






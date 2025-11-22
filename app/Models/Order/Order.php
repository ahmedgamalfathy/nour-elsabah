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

}

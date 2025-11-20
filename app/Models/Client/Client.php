<?php

namespace App\Models\Client;

use App\Models\Order\Order;
use App\Models\Client\ClientEmail;
use App\Models\Client\ClientPhone;
use App\Models\Client\ClientAdrress;
use Illuminate\Database\Eloquent\Model;
use App\Models\Points\PointsTransaction;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Client extends Model
{
    use HasFactory ,SoftDeletes,Notifiable;

    protected $guarded = [];
    protected $casts = [
        'points' => 'integer',
    ];
    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class);
    }
    public function getValidPointsAttribute(): int
    {
        return $this->pointsTransactions()
            ->where('points', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->sum('points');
    }
    public function recentPointsTransactions($limit = 10)
    {
        return $this->pointsTransactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    public function phones()
    {
        return $this->hasMany(ClientPhone::class);
    }

    public function addresses()
    {
        return $this->hasMany(ClientAdrress::class);
    }

    public function emails()
    {
        return $this->hasMany(ClientEmail::class);
    }
    public function ClientUser(){
         return $this->belongsTo(ClientUser::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    protected static function boot()
    {
        parent::boot();

        // Cascade SoftDelete
        static::deleting(function ($client) {
            if ($client->isForceDeleting()) {
                $client->phones()->forceDelete();
                $client->emails()->forceDelete();
                $client->addresses()->forceDelete();
            } else {
                $client->phones()->delete();
                $client->emails()->delete();
                $client->addresses()->delete();
            }
        });

        // Cascade Restore
        static::restoring(function ($client) {
            $client->phones()->withTrashed()->restore();
            $client->emails()->withTrashed()->restore();
            $client->addresses()->withTrashed()->restore();
        });
    }
}

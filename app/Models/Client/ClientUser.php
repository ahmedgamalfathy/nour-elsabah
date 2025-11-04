<?php

namespace App\Models\Client;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
class ClientUser extends  Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    protected $guarded =[];
    protected $table = "client_user";
    protected $guard = 'client';
    protected $hidden = [
        'password',
    ];
     /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Storage::disk('public')->url($value) : "",
        );
    }
    public function client(){
        return $this->hasOne(Client::class);
    }
}

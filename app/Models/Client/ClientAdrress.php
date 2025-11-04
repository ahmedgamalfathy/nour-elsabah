<?php

namespace App\Models\Client;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientAdrress extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'client_addresses';
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

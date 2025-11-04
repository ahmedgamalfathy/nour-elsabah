<?php

namespace App\Models\Client;

use App\Enums\IsMain;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPhone extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    protected $casts = [
        'is_main' => IsMain::class,
    ];
}

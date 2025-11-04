<?php

namespace App\Models\Client;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientEmail extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'client_emails';
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

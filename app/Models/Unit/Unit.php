<?php

namespace App\Models\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'step' => 'decimal:3',
        ];
    }

    public function products()
    {
        return $this->hasMany(\App\Models\Product\Product::class);
    }
}

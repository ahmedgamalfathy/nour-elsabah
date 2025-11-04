<?php

namespace App\Models\Product;
use App\Models\Product\Product;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductMedia extends Model
{
    use CreatedUpdatedBy,HasFactory ;
    protected $table='product_media';
    protected $fillable=['path','type','is_main', 'product_id'];
    //'product_id',
    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Storage::disk('public')->url($value) : "",
        );
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

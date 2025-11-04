<?php

namespace App\Models\Product;

use App\Traits\CreatedUpdatedBy;
use App\Enums\Product\CategoryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Category extends Model
{
    use CreatedUpdatedBy ,HasFactory;
    protected $fillable = [
        'name',
        'parent_id',
        'is_active',
        'path',
    ];

    public function casts()
    {
        return [
            'is_active' => CategoryStatus::class,
        ];
    }

    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Storage::disk('public')->url($value) : "",
        );
    }

    public function subCategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_id');
    }
    public function scopeSubCategories($query)
    {
        return $query->whereNotNull('parent_id');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category');
    }

}

<?php

namespace App\Models\Slider;

use App\Enums\IsActive;
use App\Models\Product\Product;
use App\Models\SliderItem\SliderItem;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $guarded =[];
    protected $table ='sliders';
    public function products()
    {
       return $this->belongsToMany(Product::class,'slider_items');
    }

}

<?php

namespace App\Models\Area;

use App\Models\Branch\Branch;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $guarded = [];
    protected $table = 'areas';

    public function regions()
    {
        return $this->hasMany(Area::class, 'region_id');
    }
    public function parentRegion()
    {
        return $this->belongsTo(Area::class, 'region_id');
    }
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    // public function branches()
    // {
    //     return $this->hasMany(Branch::class);
    // }

    // public function medicines()
    // {
    //     return $this->hasMany(Medicine::class);
    // }
}

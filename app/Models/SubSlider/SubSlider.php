<?php

namespace App\Models\SubSlider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubSlider extends Model
{
    use HasFactory;
  protected $table = 'sub_slider';
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Delete all slider images from storage
     */
    public function deleteImages(): void
    {
        if ($this->images && is_array($this->images)) {
            foreach ($this->images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }
    }

    /**
     * Boot method to handle model events
     */
    protected static function booted(): void
    {
        // Delete images when slider is deleted
        static::deleting(function (SubSlider $slider) {
            $slider->deleteImages();
        });
    }
}

<?php

namespace App\Services\SubSlider;

use App\Models\Slider;
use App\Models\SubSlider\SubSlider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SubSliderService
{
    /**
     * Get all sliders with pagination
     */
    public function getAll(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        return SubSlider::query()
            ->when($request->has('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->latest()
            ->cursorPaginate($perPage);
    }
   public function  sliderWebsite()
    {
        $slider= SubSlider::where('is_active',true)->first();
        return $slider;
    }
    /**
     * Get slider by ID
     */
    public function getById(int $id): SubSlider
    {
        return SubSlider::findOrFail($id);
    }

    /**
     * Create new slider
     */
    public function create(array $data): SubSlider
    {
        $imagePaths = [];

        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                $path = $image->store('sliders', 'public');
                $imagePaths[] = $path;
            }
        }

        $data['images'] = $imagePaths;

        return SubSlider::create($data);
    }

    /**
     * Update slider - delete old images and upload new ones
     */
    public function update(int $id, array $data): SubSlider
    {
        $slider = $this->getById($id);

        // If new images are provided, delete old ones and upload new
        if (isset($data['images']) && is_array($data['images'])) {
            // Delete old images from storage
            $slider->deleteImages();

            // Upload new images
            $imagePaths = [];
            foreach ($data['images'] as $image) {
                $path = $image->store('sliders', 'public');
                $imagePaths[] = $path;
            }

            $data['images'] = $imagePaths;
        }

        $slider->update($data);

        return $slider->fresh();
    }

    /**
     * Delete slider
     */
    public function delete(int $id): bool
    {
        $slider = $this->getById($id);

        // Images will be deleted automatically via model event
        return $slider->delete();
    }

    /**
     * Toggle slider active status
     */
    public function toggleActive(int $id): SubSlider
    {
        $slider = $this->getById($id);
        $slider->update(['is_active' => !$slider->is_active]);

        return $slider->fresh();
    }
}

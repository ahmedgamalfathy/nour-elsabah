<?php

namespace App\Observers;

use App\Models\Product\Category;
use Illuminate\Support\Facades\Storage;

class CategoryObserver
{
    /**
     * Handle the Category "deleting" event.
     * Deletes images before deleting subcategories.
     */
    public function deleting(Category $category)
    {
        // Delete the main category's image if it exists
        if ($category->path) {
            Storage::disk('public')->delete($category->getRawOriginal('path'));
        }

        if($category->parent_id == null){
            // Loop through subcategories and delete their images
            foreach ($category->subCategories as $subCategory) {
                if ($subCategory->path) {
                    Storage::disk('public')->delete($subCategory->getRawOriginal('path'));
                }
            }

            // Delete all subcategories
            $category->subCategories()->delete();
        }
    }
}

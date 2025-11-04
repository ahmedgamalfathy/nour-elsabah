<?php

namespace App\Services\Select;

use App\Models\Product\Category;
use Illuminate\Support\Facades\DB;

class CategorySelectService
{
    public function getCategories()
    {
        return Category::whereNull('parent_id')->get(['id as value','name as label']);
    }
    public function getSubCategories(int $parentId)
    {
        return Category::where('parent_id',$parentId)->get(['id as value','name as label']);
    }
    public function getallsubcategory()  {
        return Category::whereNotNull('parent_id')->get(['id as value','name as label']);
    }
}

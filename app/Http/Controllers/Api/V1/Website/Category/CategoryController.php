<?php

namespace App\Http\Controllers\Api\V1\Website\Category;

use App\Enums\IsActive;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Product\Category;
use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\Website\AllCategoryCollection;

class CategoryController extends Controller
{

   public function index(Request $request)  {
       $categories = Category::where('is_active',1)->get();
      return ApiResponse::success(new AllCategoryCollection(PaginateCollection::paginate($categories, $request->pageSize??10)));
   }
}

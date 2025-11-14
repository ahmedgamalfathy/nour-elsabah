<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Category;



use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Services\Category\CategoryTwoService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Resources\Client\website\AllProfileResource;
use App\Http\Resources\Category\CategoryTwo\CategoryResource;
use App\Http\Requests\Category\CategoryTwo\CreateCategoryRequest;
use App\Http\Requests\Category\CategoryTwo\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryTwo\AllCategoryCollection;

class CategoryTwoController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */

    private $categoryTwoService;
    public function __construct(CategoryTwoService $categoryTwoService){
        $this->categoryTwoService = $categoryTwoService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('permission:all_products', only:['index']),
            new Middleware('permission:create_product', only:['store']),
            new Middleware('permission:edit_product', only:['show']),
            new Middleware('permission:update_product', only:['update']),
            new Middleware('permission:destroy_product', only:['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $categories = $this->categoryTwoService->allCategoryTwo();
        return ApiResponse::success(new AllCategoryCollection(PaginateCollection::paginate($categories, $request->pageSize??10)));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategoryRequest $createCategoryRequest)
    {
        $this->categoryTwoService->createCategoryTwo($createCategoryRequest->validated());
        return ApiResponse::success([], __('crud.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id ,Request $request)
    {
        $category= $this->categoryTwoService->editCategoryTwo($id);
        return ApiResponse::success(new CategoryResource($category));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $updateCategoryRequest, string $id)
    {
        $this->categoryTwoService->updateCategoryTwo($id,$updateCategoryRequest->validated());
        return ApiResponse::success([], __('crud.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->categoryTwoService->deleteCategoryTwo($id);
        return ApiResponse::success([], __('crud.deleted'));
    }
}

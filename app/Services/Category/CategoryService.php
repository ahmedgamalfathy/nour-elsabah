<?php

namespace App\Services\Category;

use App\Models\Product\Category;
use App\Enums\Product\CategoryStatus;
use Spatie\QueryBuilder\QueryBuilder;
use App\Services\Upload\UploadService;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use App\Filters\Category\FilterCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryService{

    protected $uploadService;
    protected $subCategoryService;


    public function __construct(UploadService $uploadService, SubCategoryService $subCategoryService)
    {
        $this->uploadService = $uploadService;
        $this->subCategoryService = $subCategoryService;

    }

    public function allCategories()
    {
        $categories = QueryBuilder::for(Category::class)
        ->allowedFilters([
            AllowedFilter::custom('search', new FilterCategory()),
            AllowedFilter::exact('isActive', 'is_active')
        ])
        ->mainCategories()
        ->with('subCategories')
        ->get();

        return $categories;

    }

    public function createCategory(array $categoryData): Category
    {

        $path = isset($categoryData['path'])? $this->uploadService->uploadFile($categoryData['path'], 'categories'):null;

        $category = Category::create([
            'name' => $categoryData['name'],
            'is_active' => CategoryStatus::from($categoryData['isActive'])->value,
            'path' => $path,
            'parent_id' => null,
        ]);

        if (isset($categoryData['subCategories'])) {
            foreach ($categoryData['subCategories'] as $subCategoryData) {
                $this->subCategoryService->createSubCategory([
                    'parentId' => $category->id,
                    ...$subCategoryData
                ]);
            }
        }

        return $category;

    }

    public function editCategory(int $categoryId)
    {
       $category= Category::with('subCategories')->findOrFail($categoryId);
        if(!$category){
           throw new ModelNotFoundException();
        }
        return $category;
    }

    public function updateCategory(int $id,array $categoryData)
    {

        $path = null;

        $category = Category::find($id);
        if(isset($categoryData['path'])){
            $path = $this->uploadService->uploadFile($categoryData['path'], 'categories');
            if($category->path){
                Storage::disk('public')->delete($category->getRawOriginal('path'));
            }
            $category->path = $path;
        }
        $category->name = $categoryData['name'];
        $category->is_active = CategoryStatus::from($categoryData['isActive'])->value;
        $category->save();

        return $category;

    }


    public function deleteCategory(int $categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            throw new ModelNotFoundException();
        }
        $category->delete();
    }

}

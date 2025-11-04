<?php

namespace App\Services\Category;

use App\Enums\Product\CategoryStatus;
use App\Models\Product\Category;
use App\Services\Upload\UploadService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SubCategoryService{

    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function allSubCategories()
    {
        $subCategories = QueryBuilder::for(Category::class)
        ->allowedFilters([
            AllowedFilter::exact('categoryId', 'parent_id')
        ])
        ->SubCategories()
        ->get();

        return $subCategories;

    }

    public function createSubCategory(array $subCategoryData)
    {
        $path = isset($subCategoryData['subCategoryPath']) ? $this->uploadService->uploadFile($subCategoryData['subCategoryPath'], 'categories'):null;
        $subCategory = Category::create([
            'name' => $subCategoryData['subCategoryName'],
            'is_active' => CategoryStatus::from($subCategoryData['isActive'])->value,
            'path' => $path,
            'parent_id' => $subCategoryData['parentId'],
        ]);

        return $subCategory;

    }

    public function editSubCategory(int $subCategoryId)
    {
        $subCategory = Category::findOrFail($subCategoryId);
        if(!$subCategory){
            throw new ModelNotFoundException();
        }
        return $subCategory;
    }

    public function updateSubCategory(int $id,array $subCategoryData)
    {

        $subCategory = Category::find($id);
        $path = null;
        if(isset($subCategoryData['subCategoryPath'])){
            $path = isset($subCategoryData['subCategoryPath'])? $this->uploadService->uploadFile($subCategoryData['subCategoryPath'], 'categories'):null;
            if($subCategory->path){
                Storage::disk('public')->delete($subCategory->getRawOriginal('path'));
            }
            $subCategory->path = $path;
        }

        $subCategory->name = $subCategoryData['subCategoryName'];
        $subCategory->is_active = CategoryStatus::from($subCategoryData['isActive'])->value;
        $subCategory->save();

        return $subCategory;

    }


    public function deleteSubCategory(int $subCategoryId)
    {
       $subCategory= Category::find($subCategoryId);
       if(!$subCategory){
        throw new ModelNotFoundException();
       }
       $subCategory->delete();

    }

}

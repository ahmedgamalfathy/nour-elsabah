<?php
namespace App\Services\Category;
use App\Models\Product\Category;
use App\Models\Product\CategoryTwo;
use App\Enums\Product\CategoryStatus;
use Spatie\QueryBuilder\QueryBuilder;
use App\Services\Upload\UploadService;
use function PHPUnit\Framework\isNull;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Filters\Category\FilterCategory;
use App\Filters\Category\FilterCategoryTwo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryTwoService {
    protected $uploadService;


    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    public function allCategoryTwo()
    {
        $categories = QueryBuilder::for(CategoryTwo::class)
        ->allowedFilters([
            AllowedFilter::custom('search', new FilterCategory()),
            AllowedFilter::exact('isActive', 'is_active'),
            // AllowedFilter::exact('parentId', 'parent_id')
        ])
        ->mainCategories()
        ->with('SubCategories')
        ->orderByDesc('id')
        ->get();
        return $categories;
    }
    public function editCategoryTwo(int $categoryId){
        $category= CategoryTwo::with('SubCategories')->findOrFail($categoryId);
        if(!$category){
           throw new ModelNotFoundException();
        }
        return $category;
    }
    public function createCategoryTwo(array $categoryData)
    {
        $path = isset($categoryData['path'])? $this->uploadService->uploadFile($categoryData['path'], 'categories'):null;
        $category = CategoryTwo::create([
            'name' => $categoryData['name'],
            'is_active' => CategoryStatus::from($categoryData['isActive'])->value,
            'path' => $path,
            'parent_id' => $categoryData['parentId']??null,
        ]);
        return $category;

    }
    public function updateCategoryTwo(int $id,array $categoryData){
        $category = Category::find($id);
        if(isset($categoryData['path'])){
            $path = $this->uploadService->uploadFile($categoryData['path'], 'categories');
            if($category->path){
                Storage::disk('public')->delete($category->getRawOriginal('path'));
            }
            $category->path = $path;
        }
        // if($categoryData['parentId'] == null){
        //     if($category->parent_id == null){
        //         $category->parent_id = null;
        //     }else{
        //         Category::where('parent_id', $category->id)->delete();
        //         $category->parent_id = null;
        //     }
        // }else {
        //     if($category->parent_id == null ){
        //         // Get a default category to move products to
        //         $defaultCategory = Category::where('id', '!=', $id)->first();
        //         if($defaultCategory) {
        //             // Update the pivot table to point to the default category
        //             $category->products()->updateExistingPivot(
        //                 $category->products->pluck('id')->toArray(),
        //                 ['category_id' => $defaultCategory->id]
        //             );
        //         }
        //         // Now we can safely delete child categories
        //         Category::where('parent_id', $category->id)->delete();
        //     }
        //     $category->parent_id = $categoryData['parentId'] ?? null;
        // }
        $category->parent_id = $categoryData['parentId'] ?? null;
        $category->name = $categoryData['name'];
        $category->is_active = CategoryStatus::from($categoryData['isActive'])->value;

        $category->save();

        return $category;
    }
    public function deleteCategoryTwo(int $id){
        $category = Category::find($id);
        //parentId null , parentId = 1
        if(isNull($category->parent_id)){
            $categoriesPath= Category::where('parent_id',$category->id)->get();
            foreach ($categoriesPath as $categoryPath) {
                if ($categoryPath->path) {
                    Storage::disk('public')->delete($categoryPath->getRawOriginal('path'));
                }
                Category::where('parent_id', $category->id)->delete();
            }
            $category->delete();
        }else{
            if ($category->path) {
                Storage::disk('public')->delete($category->getRawOriginal('path'));
            }
            $category->delete();
        }
    }


}

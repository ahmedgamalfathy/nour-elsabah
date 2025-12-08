<?php
namespace App\Services\Product;

use App\Helpers\ApiResponse;
use App\Models\Product\Product;
use Spatie\QueryBuilder\QueryBuilder;
use App\Enums\Product\LimitedQuantity;
use App\Services\Upload\UploadService;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\Product\FilterProduct;
use App\Services\ProductMedia\ProductMediaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use function PHPUnit\Framework\isEmpty;

class ProductService
{
    public  $productMediaService;
    public $uploadService;
    public function __construct(ProductMediaService $productMediaService ,UploadService $uploadService)
    {
        $this->productMediaService =$productMediaService;
        $this->uploadService =$uploadService;
    }
    public function allProducts(){
        return QueryBuilder::for(Product::class)
        ->defaultSort('-created_at')
        ->allowedFilters([
            AllowedFilter::custom('search', new FilterProduct),
            AllowedFilter::exact('status'),
        ])
        ->orderBy('created_at', 'desc')
        ->get();
    }
    public function createProduct(array $data){
        // dd($data['specifications']);
        $product= Product::create([
            'name'=>$data['name'],
            'price'=>$data['price'],
            'status'=>$data['status'],
            'description'=>$data['description']??null,
            'category_id'=>$data['categoryId']??null,
            'sub_category_id'=>$data['subCategoryId']??null,
            'quantity'=>$data['quantity']??0,
            'cost'=>$data['cost']??0,
            'specifications'=>$data['specifications']??null,
            'is_limited_quantity'=>LimitedQuantity::from($data['isLimitedQuantity'])->value
              //crossed_price, is_promotion, is_free_shipping, unit_type
            ,'crossed_price'=>$data['crossedPrice']??null,
            'is_promotion'=>$data['isPromotion']??0,
            'is_free_shipping'=>$data['isFreeShipping']??0,
            'unit_type'=>$data['unitType']
        ]);

        if (isset($data['productMedia']) && is_array($data['productMedia']) && count($data['productMedia']) > 0) {
            foreach($data['productMedia'] as $media){
                $media['productId']=$product->id;
                $this->productMediaService->createProductMedia($media);
            }
      }
        return $product;
    }
    public function editProduct(int $id){
        $product= Product::with(['category', 'productMedia'])->find($id);
        if(!$product){
            throw new ModelNotFoundException();
        }
        return $product;
    }
    public function updateProduct(int $id,array $data){
        $product= Product::find($id);
        $product->update([
            'name'=>$data['name'],
            'price'=>$data['price'],
            'status'=> $data['status'],
            'description'=>$data['description']??null,
            'category_id'=>$data['categoryId']??null,
            'sub_category_id'=>$data['subCategoryId']??null,
            'quantity'=>$data['quantity']??0,
            'cost'=>$data['cost']??0,
            "specifications"=>$data["specifications"]??null,
            'is_limited_quantity'=>LimitedQuantity::from($data['isLimitedQuantity'])->value
                //crossed_price, is_promotion, is_free_shipping, unit_type
            ,'crossed_price'=>$data['crossedPrice']??0,
            'is_promotion'=>$data['isPromotion']??0,
            'is_free_shipping'=>$data['isFreeShipping']??0,
            'unit_type'=>$data['unitType']
        ]);
        return $product;
    }
    public function deleteProduct(int $id){
        $product=Product::find($id);
        if(!$product){
           throw new ModelNotFoundException();
        }
        $product->delete();
    }

}

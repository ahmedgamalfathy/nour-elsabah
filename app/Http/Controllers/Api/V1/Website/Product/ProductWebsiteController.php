<?php

namespace App\Http\Controllers\Api\V1\Website\Product;

use App\Enums\IsActive;
use App\Filters\Product\FilterProductCategory;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filters\Product\FilterProduct;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\Product\ProductService;
use App\Enums\ResponseCode\HttpStatusCode;

use App\Http\Resources\Product\Website\ProductResource;
use App\Http\Resources\Product\Website\AllProductResource;
use App\Http\Resources\Product\Website\AllProductCollection;
use App\Models\Product\Category;

class ProductWebsiteController  extends Controller
{
    public $productService;
    public function __construct( ProductService $productService)
    {
        $this->productService =$productService;
    }
    public function ShippingFreeProducts(){
        $products = Product::where('status',1)->where('is_free_shipping',1)->orderBy('created_at','desc')->limit(15)->get();
        return ApiResponse::success(AllProductResource::collection($products));
    }
    public function discountProducts(){
        $products = Product::where('status',1)->where('crossed_price','!=',0)->orderBy('created_at','desc')->limit(10)->get();
        return ApiResponse::success(AllProductResource::collection($products));
    }
    public function latestProducts()
    {
        $products = Product::where('status',1)->orderBy('created_at','desc')->limit(10)->get();
        return ApiResponse::success(AllProductResource::collection($products));
    }
    public function index(Request $request)
    {
        $categoryActive =Category::where('is_active',IsActive::ACTIVE->value)->pluck('id');

         $products= QueryBuilder::for(Product::class)
         ->allowedFilters(['status',
            AllowedFilter::custom('categoryId', new FilterProductCategory),
            AllowedFilter::custom('search', new FilterProduct),
            AllowedFilter::callback('price', function ($query, $value) {
            if (is_string($value)) {
                $value = explode(',', $value); // تحويل النص إلى مصفوفة
                return $query->whereBetween('price', [$value[0], $value[1]]);
            }
            if (is_array($value) && count($value) === 2) {
                return $query->whereBetween('price', [$value[0], $value[1]]);
            }
            return $query;
            }),
         ])->where(function ($query) use ($categoryActive) {
            $query->whereIn('category_id', $categoryActive)
                  ->orWhereIn('sub_category_id', $categoryActive);
        })->where('status',1)->get();
        // return response()->json(new AllProductCollection($products));
        return ApiResponse::success(new AllProductCollection( PaginateCollection::paginate($products, $request->pageSize?$request->pageSize:10)));
    }
    public function show(int $id)
    {
      $product=Product::with(['productMedia'])->find($id);
      if(!$product){
        return  ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
      }
     $product->getSimilarProduct();
    //  $product->getFirstProductMedia();
      return ApiResponse::success(new ProductResource($product));
    }
}

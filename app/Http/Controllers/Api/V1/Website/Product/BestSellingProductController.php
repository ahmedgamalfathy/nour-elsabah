<?php

namespace App\Http\Controllers\Api\V1\Website\Product;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Resources\Product\Website\ProductResource;

class BestSellingProductController extends Controller
{
      public function BestSellingProducts()
      {
        $products = DB::table('products')
            ->join('product_media', function($join) {
                $join->on('products.id', '=', 'product_media.product_id')
                    ->where('product_media.is_main', 1);
            })
            ->select('products.id','products.name','product_media.path')
            ->limit(5)->get();
        $BestSellingProducts = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(qty) as totalQty'))
            ->join('products','order_items.product_id','=','products.id')
            ->join('product_media', function($join) {
                $join->on('order_items.product_id', '=', 'product_media.product_id')
                    ->where('product_media.is_main', 1);
            })
            ->groupBy('order_items.product_id', 'products.name', 'product_media.path')
            // ->orderBy('totalQty','desc')
            ->limit(5)
            ->select('order_items.product_id','products.name','product_media.path')
            ->get();

        if(count($BestSellingProducts ) > 0){
            return  ApiResponse::success($BestSellingProducts);
        }else{
            return ApiResponse::success($products);
        }

      }
      public function BestSellingProductsDetail(int $id)
      {
        $product=Product::with(['productMedia'])->find($id);
        if(!$product){
          return  ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
       $product->getSimilarProduct();
       $product->getFirstProductMedia();
        return ApiResponse::success(new ProductResource($product));
      }
}

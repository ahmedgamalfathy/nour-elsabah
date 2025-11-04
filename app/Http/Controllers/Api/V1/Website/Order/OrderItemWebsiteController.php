<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use App\Models\Client\ClientUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\Product\LimitedQuantity;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Resources\Order\OrderItem\Website\OrderItemResource;

class OrderItemWebsiteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }
    public function allItems(Request $request)
    {
    $auth = $request->user();
    $clientId = ClientUser::findOrFail($auth->id);
    $inCartOrder= Order::where('status',OrderStatus::IN_CART)->where('client_id',$clientId->client_id)->first();
    if(!$inCartOrder){
        return ApiResponse::success([],"There are no products in the cart.",HttpStatusCode::NOT_FOUND);
    }
    $orderItems = $inCartOrder->items;
    return ApiResponse::success(OrderItemResource::collection($orderItems));
    }
    public function editItem($id){
        $orderItem = OrderItem::findOrFail($id);
        if(!$orderItem){
         return ApiResponse::error(__('crud.not_found'));
        }
        return ApiResponse::success(new OrderItemResource($orderItem));

     }
    public function createItem(Request $request, int $orderId)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($orderId);
            $product = Product::where('id',$request->productId)->select(['cost','price'])->first();

            $existingItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $request->productId)
            ->first();

            if ($existingItem) {
            $productModel = $existingItem->product;
            $newQty = $existingItem->qty + $request->qty;
            if ($productModel->is_limited_quantity == LimitedQuantity::LIMITED) {
                if ($productModel->quantity < $request->qty) {
                    DB::rollBack();
                    return response()->json(['error' => 'No available quantity', 'product' => [
                        'productId' => $productModel->id,
                        'quantity' => $productModel->quantity,
                        'name' => $productModel->name
                    ]], 422);
                }
                $product->decrement('quantity', $request->qty);
            }
            $existingItem->qty = $newQty;
            $existingItem->save();
            }else {
            $item = new OrderItem([
                'order_id' => $order->id,
                'product_id' => $request->productId,
                'qty' => $request->qty,
                'price' =>  $product->price,
                'cost' =>  $product->cost,
            ]);
            $product = $item->product;
            if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                if ($product->quantity < $item->qty) {
                    DB::rollBack();
                    return response()->json(['error' => 'No available quantity', 'product' => [
                        'productId' => $product->id,
                        'quantity' => $product->quantity,
                        'name' => $product->name
                    ]], 422);
                }
                    $product->decrement('quantity', $item->qty);
            }
            $item->save();
            }
            $this->recalculateOrderTotals($order);
            DB::commit();
            return ApiResponse::success(__('crud.created'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'),$e->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }


    public function updateItem(Request $request, int $itemId)
    {
        DB::beginTransaction();
        try {
            $item = OrderItem::findOrFail($itemId);
            $order = $item->order;

            // رجع الكمية القديمة قبل التحديث
            if ($item->product->is_limited_quantity == LimitedQuantity::LIMITED) {
                $item->product->increment('quantity', $item->qty);
            }

            $item->update([
                'qty' => $request->qty,
                'price' => $item->product->price,
                'cost' => $item->product->cost,
            ]);

            $product = $item->product;
            if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                if ($product->quantity < $item->qty) {
                    DB::rollBack();
                    return response()->json(['error' => 'No available quantity', 'product' => [
                        'productId' => $product->id,
                        'quantity' => $product->quantity,
                        'name' => $product->name
                    ]], 422);
                }
                $product->decrement('quantity', $item->qty);
            }

            $this->recalculateOrderTotals($order);
            DB::commit();

            return ApiResponse::success(__('crud.updated'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'),$e->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }


    public function deleteItem(int $itemId)
    {
        DB::beginTransaction();
        try {
            $item = OrderItem::findOrFail($itemId);
            $order = $item->order;

            if ($item->product->is_limited_quantity == LimitedQuantity::LIMITED) {
                $item->product->increment('quantity', $item->qty);
            }

            $item->delete();
            if ($order->items()->count() === 0) {
                $order->delete();
            } else {
                $this->recalculateOrderTotals($order);
            }

            DB::commit();
           return ApiResponse::success(__('crud.deleted'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'),$e->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ✅ إعادة حساب إجمالي الطلب بعد أي تعديل
     */
    private function recalculateOrderTotals(Order $order)
    {
        $totalPrice = $order->items->sum(fn($i) => $i->price * $i->qty);
        $totalCost = $order->items->sum(fn($i) => $i->cost * $i->qty);

        $discount = $order->discount ?? 0;
        $totalPriceAfterDiscount = match ($order->discount_type) {
            DiscountType::PERCENTAGE => $totalPrice - ($totalPrice * ($discount / 100)),
            DiscountType::FIXCED => $totalPrice - $discount,
            default => $totalPrice,
        };

        $order->update([
            'price' => $totalPrice,
            'total_cost' => $totalCost,
            'price_after_discount' => $totalPriceAfterDiscount,
        ]);
    }
}


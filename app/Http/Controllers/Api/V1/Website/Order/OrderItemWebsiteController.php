<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Enums\Order\OrderStatus;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Exceptions\InsufficientStockException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Website\Order\CreateOrderItemRequest;
use App\Http\Requests\Api\V1\Website\Order\UpdateOrderItemRequest;
use App\Http\Resources\Order\OrderItem\Website\OrderItemResource;
use App\Models\Client\ClientUser;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderItemWebsiteController extends Controller implements HasMiddleware
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }

    public function allItems(Request $request)
    {
        $clientId = ClientUser::findOrFail($request->user()->id)->client_id;
        $inCartOrder = Order::where('status', OrderStatus::IN_CART)
            ->where('client_id', $clientId)
            ->first();

        if (! $inCartOrder) {
            return ApiResponse::success([], 'There are no products in the cart.', HttpStatusCode::NOT_FOUND);
        }

        return ApiResponse::success(OrderItemResource::collection($inCartOrder->items));
    }

    public function createItem(CreateOrderItemRequest $request, int $orderId)
    {
        try {
            return DB::transaction(function () use ($request, $orderId) {
                $data = $request->validated();
                $order = Order::whereKey($orderId)
                    ->where('client_id', $request->user()->client_id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($order->isLockedForCheckout()) {
                    return ApiResponse::error('Order is locked for checkout.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                $product = Product::with('unit')->findOrFail($data['productId']);

                $existingItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($existingItem) {
                    $existingItem->update(['qty' => (float) $existingItem->qty + (float) $data['qty']]);
                } else {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'qty' => $data['qty'],
                        'price' => $product->price,
                        'cost' => $product->cost,
                    ]);
                }

                $this->inventoryService->assertStockAvailable($order->fresh('items.product'));

                return ApiResponse::success(__('crud.created'));
            });
        } catch (InsufficientStockException $e) {
            return ApiResponse::error(__('crud.no_available_quantity'), [
                'product' => $e->productName,
                'availableQuantity' => $e->availableQuantity,
            ], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function updateItem(UpdateOrderItemRequest $request, int $itemId)
    {
        try {
            return DB::transaction(function () use ($request, $itemId) {
                $data = $request->validated();
                $item = OrderItem::with(['order', 'product.unit'])
                    ->whereHas('order', fn ($query) => $query->where('client_id', $request->user()->client_id))
                    ->lockForUpdate()
                    ->findOrFail($itemId);
                if ($item->order->isLockedForCheckout()) {
                    return ApiResponse::error('Order is locked for checkout.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                $item->update([
                    'qty' => $data['qty'],
                    'price' => $item->product->price,
                    'cost' => $item->product->cost,
                ]);

                $this->inventoryService->assertStockAvailable($item->order->fresh('items.product'));

                return ApiResponse::success(__('crud.updated'));
            });
        } catch (InsufficientStockException $e) {
            return ApiResponse::error(__('crud.no_available_quantity'), [
                'product' => $e->productName,
                'availableQuantity' => $e->availableQuantity,
            ], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteItem(Request $request, int $itemId)
    {
        try {
            return DB::transaction(function () use ($request, $itemId) {
                $item = OrderItem::with('order')
                    ->whereHas('order', fn ($query) => $query->where('client_id', $request->user()->client_id))
                    ->lockForUpdate()
                    ->findOrFail($itemId);
                $order = $item->order;
                if ($order->isLockedForCheckout()) {
                    return ApiResponse::error('Order is locked for checkout.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                $item->delete();

                if ($order->items()->count() === 0) {
                    $order->delete();
                }

                return ApiResponse::success(__('crud.deleted'));
            });
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

}

<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Enums\Product\LimitedQuantity;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Models\Client\ClientUser;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Rules\ValidStepQuantity;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderItem\Website\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderItemWebsiteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }

    // ─── Read ─────────────────────────────────────────────────────────────────

    public function allItems(Request $request)
    {
        $clientId    = ClientUser::findOrFail($request->user()->id)->client_id;
        $inCartOrder = Order::where('status', OrderStatus::IN_CART)
                            ->where('client_id', $clientId)
                            ->first();

        if (! $inCartOrder) {
            return ApiResponse::success([], 'There are no products in the cart.', HttpStatusCode::NOT_FOUND);
        }

        return ApiResponse::success(OrderItemResource::collection($inCartOrder->items));
    }

    public function editItem(int $id)
    {
        return ApiResponse::success(new OrderItemResource(OrderItem::findOrFail($id)));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function createItem(Request $request, int $orderId)
    {
        DB::beginTransaction();
        try {
            $order   = Order::findOrFail($orderId);
            $product = Product::with('unit')->findOrFail($request->productId);

            // Validate step & min quantity
            if ($error = $this->validateQuantity($product, (float) $request->qty)) {
                return ApiResponse::error($error, [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $existingItem = OrderItem::where('order_id', $order->id)
                ->where('product_id', $request->productId)
                ->first();

            if ($existingItem) {
                if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                    if ((float) $product->quantity < (float) $request->qty) {
                        DB::rollBack();
                        return $this->insufficientStockResponse($product);
                    }
                    $product->decrement('quantity', $request->qty);
                }
                $existingItem->update(['qty' => (float) $existingItem->qty + (float) $request->qty]);
            } else {
                if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                    if ((float) $product->quantity < (float) $request->qty) {
                        DB::rollBack();
                        return $this->insufficientStockResponse($product);
                    }
                    $product->decrement('quantity', $request->qty);
                }
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'qty'        => $request->qty,
                    'price'      => $product->price,
                    'cost'       => $product->cost,
                ]);
            }

            $this->recalculateOrderTotals($order->fresh('items'));
            DB::commit();

            return ApiResponse::success(__('crud.created'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function updateItem(Request $request, int $itemId)
    {
        DB::beginTransaction();
        try {
            $item    = OrderItem::findOrFail($itemId);
            $order   = $item->order;
            $product = $item->product->load('unit');

            // Validate step & min quantity
            if ($error = $this->validateQuantity($product, (float) $request->qty)) {
                return ApiResponse::error($error, [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            // Restore old stock before applying new qty
            if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                $product->increment('quantity', $item->qty);
            }

            $item->update([
                'qty'   => $request->qty,
                'price' => $product->price,
                'cost'  => $product->cost,
            ]);

            if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                if ((float) $product->quantity < (float) $item->qty) {
                    DB::rollBack();
                    return $this->insufficientStockResponse($product);
                }
                $product->decrement('quantity', $item->qty);
            }

            $this->recalculateOrderTotals($order->fresh('items'));
            DB::commit();

            return ApiResponse::success(__('crud.updated'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function deleteItem(int $itemId)
    {
        DB::beginTransaction();
        try {
            $item    = OrderItem::findOrFail($itemId);
            $order   = $item->order;

            if ($item->product->is_limited_quantity == LimitedQuantity::LIMITED) {
                $item->product->increment('quantity', $item->qty);
            }

            $item->delete();

            if ($order->items()->count() === 0) {
                $order->delete();
            } else {
                $this->recalculateOrderTotals($order->fresh('items'));
            }

            DB::commit();
            return ApiResponse::success(__('crud.deleted'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Returns an error message if qty fails min_quantity or quantity_step checks, null otherwise.
     */
    private function validateQuantity(Product $product, float $qty): ?string
    {
        $min  = (float) $product->min_quantity;
        $step = (float) $product->quantity_step;
        $unit = $product->unit?->name ?? '';

        if ($qty < $min) {
            return "الكمية يجب أن لا تقل عن {$min} {$unit}.";
        }

        if ($step > 0) {
            $remainder  = fmod($qty, $step);
            $isMultiple = $remainder < 0.0001 || ($step - $remainder) < 0.0001;

            if (! $isMultiple) {
                return "الكمية غير متوافقة مع وحدة بيع المنتج. أقل وحدة زيادة هي {$step} {$unit}.";
            }
        }

        return null;
    }

    /**
     * Recalculate order price, cost, and price_after_discount.
     * Uses decimal-safe multiplication: total = price * qty.
     */
    private function recalculateOrderTotals(Order $order): void
    {
        $totalPrice = $order->items->reduce(fn($carry, $i) => $carry + ($i->price * $i->qty), 0.0);
        $totalCost  = $order->items->reduce(fn($carry, $i) => $carry + ($i->cost  * $i->qty), 0.0);

        $discount = (float) ($order->discount ?? 0);
        $totalPriceAfterDiscount = match ($order->discount_type) {
            DiscountType::PERCENTAGE => $totalPrice - ($totalPrice * ($discount / 100)),
            DiscountType::FIXCED     => $totalPrice - $discount,
            default                  => $totalPrice,
        };

        $order->update([
            'price'                => $totalPrice,
            'total_cost'           => $totalCost,
            'price_after_discount' => $totalPriceAfterDiscount,
        ]);
    }

    private function insufficientStockResponse(Product $product): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error'   => 'No available quantity',
            'product' => [
                'productId' => $product->id,
                'quantity'  => $product->quantity,
                'name'      => $product->name,
            ],
        ], 422);
    }
}

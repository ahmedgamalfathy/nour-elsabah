<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use Throwable;
use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use App\Events\CreatedOrderEvent;
use App\Enums\Product\LimitedQuantity;
use App\Exceptions\InsufficientStockException;
use App\Rules\ValidStepQuantity;
use App\Services\Coupon\CouponService;
use App\Services\Points\PointsService;
use App\Services\Order\OrderItemService;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Resources\Order\Website\OrderResource;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AuthOrderController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly OrderItemService $orderItemService,
        private readonly CouponService    $couponService,
        private readonly PointsService    $pointsService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }

    // ─── Store (create cart order) ────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'orderItems'             => ['required', 'array', 'min:1'],
            'orderItems.*.productId' => ['required', 'integer', 'exists:products,id'],
            'orderItems.*.qty'       => [
                'required',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    preg_match('/orderItems\.(\d+)\.qty/', $attribute, $matches);
                    $productId = $request->input("orderItems.{$matches[1]}.productId");
                    if ($productId) {
                        (new ValidStepQuantity((int) $productId))->validate($attribute, $value, $fail);
                    }
                },
            ],
        ]);

        try {
            DB::beginTransaction();

            $auth   = $request->user();
            $client = Client::findOrFail($auth->client_id);

            $order = Order::create([
                'discount'          => 0.00,
                'discount_type'     => DiscountType::NO_DISCOUNT->value,
                'client_phone_id'   => null,
                'client_email_id'   => null,
                'client_address_id' => null,
                'client_id'         => $client->id,
                'status'            => OrderStatus::IN_CART->value,
            ]);

            $totalPrice = 0.0;
            $totalCost  = 0.0;

            foreach ($data['orderItems'] as $itemData) {
                $item = $this->orderItemService->createOrderItem([
                    'orderId' => $order->id,
                    ...$itemData,
                ]);

                // Lock the product row to prevent race conditions
                $product = $item->product()->lockForUpdate()->first();

                if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                    if ((float) $product->quantity < (float) $item->qty) {
                        throw new InsufficientStockException($product->name, (float) $product->quantity);
                    }
                    // Decimal-safe decrement (supports 0.250, 0.500, etc.)
                    $product->decrement('quantity', $item->qty);
                }

                // total = unit_price × decimal_qty
                $totalPrice += (float) $item->price * (float) $item->qty;
                $totalCost  += (float) $item->cost  * (float) $item->qty;
            }

            $order->update([
                'price'                => $totalPrice,
                'total_cost'           => $totalCost,
                'price_after_discount' => $totalPrice, // no discount yet at cart stage
            ]);

            DB::commit();

            return ApiResponse::success(new OrderResource($order->load('items')));
        } catch (InsufficientStockException $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), [
                'product'           => $e->productName,
                'availableQuantity' => $e->availableQuantity,
            ], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        try {
            $order = Order::with(['items', 'items.product'])->findOrFail($id);
            return ApiResponse::success(new OrderResource($order));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }
    }

    // ─── Update (attach client contact info before checkout) ─────────────────

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'client.clientPhoneId'   => ['required', 'exists:client_phones,id'],
            'client.clientEmailId'   => ['required', 'exists:client_emails,id'],
            'client.clientAddressId' => ['required', 'exists:client_addresses,id'],
        ]);

        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);

            if ($order->status === OrderStatus::CHECKOUT) {
                return ApiResponse::error('Order already checked out.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $order->update([
                'client_phone_id'   => $data['client']['clientPhoneId'],
                'client_email_id'   => $data['client']['clientEmailId'],
                'client_address_id' => $data['client']['clientAddressId'],
                'status'            => OrderStatus::IN_CART->value,
            ]);

            DB::commit();

            return ApiResponse::success(new OrderResource($order->fresh()));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            DB::rollBack();
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Coupon ───────────────────────────────────────────────────────────────

    public function couponCart(Request $request)
    {
        $data = $request->validate([
            'couponCode' => ['required', 'exists:coupons,code'],
            'orderId'    => ['required', 'exists:orders,id'],
        ]);

        try {
            DB::beginTransaction();

            $auth   = $request->user();
            $client = Client::findOrFail($auth->client_id);
            $order  = Order::findOrFail($data['orderId']);

            $couponValidation = $this->couponService->validateCoupon(
                $data['couponCode'],
                $client->id,
                $order->price_after_discount
            );

            if (! $couponValidation['valid']) {
                return ApiResponse::error($couponValidation['message'] ?? __('crud.invalid_coupon'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $couponDiscount = $couponValidation['discount'];

            $this->couponService->applyCoupon(
                $couponValidation['coupon'],
                $order,
                $client->id,
                $couponDiscount
            );

            $order->price_after_discount = max(0, $order->price_after_discount - $couponDiscount);
            $order->save();

            DB::commit();

            return ApiResponse::success([
                'valueCoupon'        => $couponDiscount,
                'priceAfterCoupon'   => $order->price_after_discount,
                'priceAfterDiscount' => $order->price_after_discount,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Cash on Delivery ─────────────────────────────────────────────────────

    public function cashOnDelivery(Request $request)
    {
        try {
            if (! \App\Models\Setting\Setting::get('payment.cash_on_delivery.enabled', true)) {
                return ApiResponse::error('الدفع عند الاستلام غير متاح حالياً', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $data  = $request->validate(['orderId' => ['required', 'exists:orders,id']]);
            $order = Order::findOrFail($data['orderId']);

            DB::beginTransaction();

            $order->status = OrderStatus::CASHONDELIVERY->value;
            $order->save();

            broadcast(new CreatedOrderEvent($order));

            DB::commit();

            return ApiResponse::success([], __('crud.updated'));
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Points ───────────────────────────────────────────────────────────────

    public function myPoints(Request $request)
    {
        try {
            $client = Client::findOrFail($request->user()->client_id);

            return ApiResponse::success([
                'availablePoints' => $this->pointsService->getAvailablePoints($client),
                'pointsValue'     => $this->pointsService->calculatePointsValue(
                    $this->pointsService->getAvailablePoints($client)
                ),
            ]);
        } catch (Throwable $e) {
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function redeemPoints(Request $request)
    {
        try {
            $data = $request->validate([
                'orderId' => ['required', 'exists:orders,id'],
                'points'  => ['required', 'integer', 'min:1'],
            ]);

            $client = Client::findOrFail($request->user()->client_id);
            $order  = Order::findOrFail($data['orderId']);

            if ($order->client_id !== $client->id) {
                return ApiResponse::error('هذا الطلب لا يخصك', [], HttpStatusCode::FORBIDDEN);
            }

            if ($order->status === OrderStatus::CHECKOUT) {
                return ApiResponse::error('لا يمكن استخدام النقاط لطلب مكتمل', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $result = $this->pointsService->redeemPoints($client, $order, $data['points']);

            if (! $result['success']) {
                return ApiResponse::error($result['message'], [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            return ApiResponse::success([
                'message'          => $result['message'],
                'pointsRedeemed'   => $result['points_redeemed'],
                'discountAmount'   => $result['discount_amount'],
                'newTotal'         => $result['new_total'],
                'remainingPoints'  => $result['remaining_points'],
                'order'            => new OrderResource($order->fresh()),
            ]);
        } catch (Throwable $e) {
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function cancelPointsRedemption(Request $request)
    {
        try {
            $data = $request->validate(['orderId' => ['required', 'exists:orders,id']]);

            $client = Client::findOrFail($request->user()->client_id);
            $order  = Order::findOrFail($data['orderId']);

            if ($order->client_id !== $client->id) {
                return ApiResponse::error('هذا الطلب لا يخصك', [], HttpStatusCode::FORBIDDEN);
            }

            if ($order->points_redeemed <= 0) {
                return ApiResponse::error('لم يتم استخدام نقاط في هذا الطلب', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $this->pointsService->cancelPointsRedemption($order);

            return ApiResponse::success([
                'message' => 'تم إلغاء استخدام النقاط بنجاح',
                'order'   => new OrderResource($order->fresh()),
            ]);
        } catch (Throwable $e) {
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}

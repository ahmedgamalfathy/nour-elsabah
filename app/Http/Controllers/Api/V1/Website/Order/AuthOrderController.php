<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Enums\Order\OrderStatus;
use App\Events\CreatedOrderEvent;
use App\Exceptions\InsufficientStockException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Website\Order\ApplyOrderCouponRequest;
use App\Http\Requests\Api\V1\Website\Order\OrderIdRequest;
use App\Http\Requests\Api\V1\Website\Order\RedeemPointsRequest;
use App\Http\Requests\Api\V1\Website\Order\StoreAuthenticatedOrderRequest;
use App\Http\Requests\Api\V1\Website\Order\UpdateOrderContactRequest;
use App\Http\Resources\Order\Website\OrderResource;
use App\Models\Client\Client;
use App\Models\Order\Order;
use App\Services\Coupon\CouponService;
use App\Services\Order\CheckoutService;
use App\Services\Points\PointsService;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthOrderController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly CouponService $couponService,
        private readonly PointsService $pointsService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }

    /**
     * Create an authenticated cart through the unified website order pipeline.
     */
    public function store(StoreAuthenticatedOrderRequest $request)
    {
        try {
            $order = $this->checkoutService->execute($request->validated(), $request->user()->client_id);

            return ApiResponse::success(new OrderResource($order));
        } catch (InsufficientStockException $e) {
            return ApiResponse::error(__('crud.no_available_quantity'), [
                'product' => $e->productName,
                'availableQuantity' => $e->availableQuantity,
            ], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        try {
            $order = Order::with(['items', 'items.product'])
                ->where('id', $id)
                ->where('client_id', request()->user()->client_id)
                ->firstOrFail();

            return ApiResponse::success(new OrderResource($order));
        } catch (ModelNotFoundException) {
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }
    }

    public function update(UpdateOrderContactRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $id, $data) {
                $order = Order::where('id', $id)
                    ->where('client_id', $request->user()->client_id)
                    ->firstOrFail();

                if ($order->isLockedForCheckout()) {
                    return ApiResponse::error('Order is locked for checkout.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                $order->update([
                    'client_phone_id'   => $data['client']['clientPhoneId'],
                    'client_email_id'   => $data['client']['clientEmailId'],
                    'client_address_id' => $data['client']['clientAddressId'],
                    'status'            => OrderStatus::IN_CART->value,
                ]);

                return ApiResponse::success(new OrderResource($order->fresh()));
            });
        } catch (ModelNotFoundException) {
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function couponCart(ApplyOrderCouponRequest $request)
    {
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data) {
                $client = Client::findOrFail($request->user()->client_id);
                $order = Order::where('id', $data['orderId'])
                    ->where('client_id', $client->id)
                    ->firstOrFail();

                if ($order->isLockedForCheckout()) {
                    return ApiResponse::error('Order is locked for checkout.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

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

                $order->recalculateTotals();

                return ApiResponse::success([
                    'valueCoupon' => $couponDiscount,
                    'priceAfterCoupon' => $order->price_after_discount,
                    'priceAfterDiscount' => $order->price_after_discount,
                ]);
            });
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function cashOnDelivery(OrderIdRequest $request)
    {
        try {
            if (! \App\Models\Setting\Setting::get('payment.cash_on_delivery.enabled', true)) {
                return ApiResponse::error('Cash on delivery is not available.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $data = $request->validated();

            return DB::transaction(function () use ($request, $data) {
                $order = Order::where('id', $data['orderId'])
                    ->where('client_id', $request->user()->client_id)
                    ->firstOrFail();

                if ($order->isLockedForCheckout()) {
                    return ApiResponse::error('Order is locked for checkout.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                $order->update(['status' => OrderStatus::CASHONDELIVERY->value]);

                broadcast(new CreatedOrderEvent($order));

                return ApiResponse::success([], __('crud.updated'));
            });
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function myPoints(Request $request)
    {
        try {
            $client = Client::findOrFail($request->user()->client_id);

            return ApiResponse::success([
                'availablePoints' => $this->pointsService->getAvailablePoints($client),
                'pointsValue' => $this->pointsService->calculatePointsValue(
                    $this->pointsService->getAvailablePoints($client)
                ),
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function redeemPoints(RedeemPointsRequest $request)
    {
        try {
            $data = $request->validated();

            $client = Client::findOrFail($request->user()->client_id);
            $order = Order::where('id', $data['orderId'])
                ->where('client_id', $client->id)
                ->firstOrFail();

            if ($order->isLockedForCheckout()) {
                return ApiResponse::error('Order is locked for checkout.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $result = $this->pointsService->redeemPoints($client, $order, $data['points']);

            if (! $result['success']) {
                return ApiResponse::error($result['message'], [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            return ApiResponse::success([
                'message' => $result['message'],
                'pointsRedeemed' => $result['points_redeemed'],
                'discountAmount' => $result['discount_amount'],
                'newTotal' => $result['new_total'],
                'remainingPoints' => $result['remaining_points'],
                'order' => new OrderResource($order->fresh()),
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function cancelPointsRedemption(OrderIdRequest $request)
    {
        try {
            $data = $request->validated();

            $client = Client::findOrFail($request->user()->client_id);
            $order = Order::where('id', $data['orderId'])
                ->where('client_id', $client->id)
                ->firstOrFail();

            if ((int) $order->points_redeemed <= 0) {
                return ApiResponse::error('No points were redeemed for this order.', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $this->pointsService->cancelPointsRedemption($order);

            return ApiResponse::success([
                'message' => 'Points redemption cancelled successfully.',
                'order' => new OrderResource($order->fresh()),
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}

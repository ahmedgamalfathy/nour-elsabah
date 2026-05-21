<?php

namespace App\Http\Controllers\Api\V1\Website\Order;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Website\Order\GuestCheckoutRequest;
use App\Http\Resources\Order\Website\OrderResource;
use App\Services\Order\CheckoutService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
    ) {}

    public function store(GuestCheckoutRequest $request)
    {
        try {
            $order = $this->checkoutService->execute($request->validated());

            return ApiResponse::success(new OrderResource($order));
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ApiResponse::error(__('crud.server_error'));
        }
    }
}

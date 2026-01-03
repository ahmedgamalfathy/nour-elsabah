<?php

namespace App\Http\Controllers\Api\V1\Website\Coupon;

use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Coupon\CouponService;
use App\Enums\ResponseCode\HttpStatusCode;

class ValidateCouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'code' =>[ 'required','string','exists:coupons,code'],
            'orderId'=>'required|exists:orders,id'
        ]);
         $order = Order::find($data['orderId']);
        if(!$order){
          return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $auth = $request->user();
        if (!$auth) {
            return ApiResponse::error("يجب تسجيل الدخول أولاً", 401);
        }

        $result = $this->couponService->validateCoupon(
            $data['code'],
            $auth->client_id,
            $order->price_after_discount
        );

        if (!$result['valid']) {
            return ApiResponse::error($result['message'], 422);
        }

        return ApiResponse::success([
            'code' => $result['coupon']->code,
            'type' => $result['coupon']->type,
            'discount' => $result['discount'],
            'priceAfterDiscount' => max(0, $order->price_after_discount - $result['discount']),
            'priceBeforeDiscount' => $order->price_after_discount,
            'message' => $result['message']
        ]);
    }
}


<?php

namespace App\Http\Controllers\Api\V1\Website\Coupon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Coupon\CouponService;
use App\Helpers\ApiResponse;
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
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $auth = $request->user();
        if (!$auth) {
            return ApiResponse::error("يجب تسجيل الدخول أولاً", 401);
        }

        $result = $this->couponService->validateCoupon(
            $data['code'],
            $auth->client_id,
            $data['order_amount']
        );

        if (!$result['valid']) {
            return ApiResponse::error($result['message'], 422);
        }

        return ApiResponse::success([
            'code' => $result['coupon']->code,
            'type' => $result['coupon']->type,
            'discount' => $result['discount'],
            'message' => $result['message']
        ]);
    }
}


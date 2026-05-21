<?php

namespace App\Http\Requests\Api\V1\Website\Order;

use App\Http\Requests\Api\V1\BaseApiFormRequest;

class ApplyOrderCouponRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'couponCode' => ['required', 'exists:coupons,code'],
            'orderId' => ['required', 'exists:orders,id'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1\Website\Coupon;

use App\Http\Requests\Api\V1\BaseApiFormRequest;

class ValidateCouponRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'exists:coupons,code'],
            'orderId' => ['required', 'exists:orders,id'],
        ];
    }
}

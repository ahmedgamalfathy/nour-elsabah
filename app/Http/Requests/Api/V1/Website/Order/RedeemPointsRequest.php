<?php

namespace App\Http\Requests\Api\V1\Website\Order;

use App\Http\Requests\Api\V1\BaseApiFormRequest;

class RedeemPointsRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'orderId' => ['required', 'exists:orders,id'],
            'points' => ['required', 'integer', 'min:1'],
        ];
    }
}

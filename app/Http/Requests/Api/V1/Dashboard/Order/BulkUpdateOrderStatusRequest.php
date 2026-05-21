<?php

namespace App\Http\Requests\Api\V1\Dashboard\Order;

use App\Enums\Order\OrderStatus;
use App\Http\Requests\Api\V1\BaseApiFormRequest;
use Illuminate\Validation\Rules\Enum;

class BulkUpdateOrderStatusRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', 'integer', new Enum(OrderStatus::class)],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:orders,id'],
        ];
    }
}

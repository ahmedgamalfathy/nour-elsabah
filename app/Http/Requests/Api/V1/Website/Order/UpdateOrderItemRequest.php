<?php

namespace App\Http\Requests\Api\V1\Website\Order;

use App\Http\Requests\Api\V1\BaseApiFormRequest;
use App\Models\Order\OrderItem;
use App\Rules\ValidStepQuantity;

class UpdateOrderItemRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'qty' => [
                'required',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $item = OrderItem::find($this->route('itemId'));

                    if ($item) {
                        (new ValidStepQuantity((int) $item->product_id))->validate($attribute, $value, $fail);
                    }
                },
            ],
        ];
    }
}

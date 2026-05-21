<?php

namespace App\Http\Requests\Api\V1\Website\Order;

use App\Http\Requests\Api\V1\BaseApiFormRequest;
use App\Rules\ValidStepQuantity;

class CreateOrderItemRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'productId' => ['required', 'integer', 'exists:products,id'],
            'qty' => [
                'required',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $productId = $this->input('productId');

                    if ($productId) {
                        (new ValidStepQuantity((int) $productId))->validate($attribute, $value, $fail);
                    }
                },
            ],
        ];
    }
}

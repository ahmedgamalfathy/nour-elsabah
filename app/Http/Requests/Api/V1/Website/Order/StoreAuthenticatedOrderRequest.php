<?php

namespace App\Http\Requests\Api\V1\Website\Order;

use App\Http\Requests\Api\V1\BaseApiFormRequest;
use App\Rules\ValidStepQuantity;

class StoreAuthenticatedOrderRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'orderItems' => ['required', 'array', 'min:1'],
            'orderItems.*.productId' => ['required', 'integer', 'exists:products,id'],
            'orderItems.*.qty' => [
                'required',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    preg_match('/orderItems\.(\d+)\.qty/', $attribute, $matches);
                    $productId = $this->input("orderItems.{$matches[1]}.productId");

                    if ($productId) {
                        (new ValidStepQuantity((int) $productId))->validate($attribute, $value, $fail);
                    }
                },
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1\Dashboard\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Http\Requests\Api\V1\BaseApiFormRequest;
use App\Rules\ValidStepQuantity;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'discount' => ['nullable', 'numeric'],
            'discountType' => ['required', new Enum(DiscountType::class)],
            'clientId' => ['required'],
            'clientPhoneId' => ['required'],
            'clientEmailId' => ['nullable'],
            'clientAddressId' => ['required'],
            'status' => ['required', new Enum(OrderStatus::class)],
            'orderItems' => ['required', 'array'],
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

    public function messages(): array
    {
        return [
            'discountType.required' => __('validation.custom.required'),
            'clientId.required' => __('validation.custom.required'),
            'orderItems.required' => __('validation.custom.required'),
            'status.required' => __('validation.custom.required'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1\Dashboard\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Http\Requests\Api\V1\BaseApiFormRequest;
use App\Rules\ValidStepQuantity;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderRequest extends BaseApiFormRequest
{
    public function rules(): array
    {
        return [
            'discount' => ['nullable', 'numeric'],
            'discountType' => ['required', new Enum(DiscountType::class)],
            'clientPhoneId' => ['nullable'],
            'clientEmailId' => ['nullable'],
            'clientAddressId' => ['nullable'],
            'clientId' => ['required'],
            'status' => ['required', new Enum(OrderStatus::class)],
            'orderItems' => ['required', 'array'],
            'orderItems.*.productId' => ['required_if:orderItems.*.actionStatus,create', 'nullable', 'integer', 'exists:products,id'],
            'orderItems.*.orderItemId' => ['required_if:orderItems.*.actionStatus,update', 'required_if:orderItems.*.actionStatus,delete', 'nullable', 'integer'],
            'orderItems.*.actionStatus' => ['required', 'in:create,update,delete,'],
            'orderItems.*.qty' => [
                'required_unless:orderItems.*.actionStatus,delete',
                'nullable',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    preg_match('/orderItems\.(\d+)\.qty/', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    $status = $this->input("orderItems.{$index}.actionStatus");
                    $productId = $this->input("orderItems.{$index}.productId");

                    if (in_array($status, ['create', 'update'], true) && $productId) {
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

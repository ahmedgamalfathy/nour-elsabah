<?php

namespace App\Http\Requests\Order;

use App\Helpers\ApiResponse;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use App\Rules\ValidStepQuantity;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'discount'        => ['nullable', 'numeric'],
            'discountType'    => ['required', new Enum(DiscountType::class)],
            'clientPhoneId'   => ['nullable'],
            'clientEmailId'   => ['nullable'],
            'clientAddressId' => ['nullable'],
            'clientId'        => ['required'],
            'status'          => ['required', new Enum(OrderStatus::class)],
            'orderItems'      => ['required', 'array'],
            'orderItems.*.productId'    => ['required_if:orderItems.*.actionStatus,create', 'nullable', 'integer', 'exists:products,id'],
            'orderItems.*.orderItemId'  => ['required_if:orderItems.*.actionStatus,update', 'required_if:orderItems.*.actionStatus,delete', 'nullable', 'integer'],
            'orderItems.*.actionStatus' => ['required', 'in:create,update,delete,'],
            'orderItems.*.qty'          => [
                'required_unless:orderItems.*.actionStatus,delete',
                'nullable',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail) {
                    preg_match('/orderItems\.(\d+)\.qty/', $attribute, $matches);
                    $index     = $matches[1] ?? null;
                    $status    = $this->input("orderItems.{$index}.actionStatus");
                    $productId = $this->input("orderItems.{$index}.productId");

                    // Only validate step for create/update actions with a known product
                    if (in_array($status, ['create', 'update']) && $productId) {
                        (new ValidStepQuantity((int) $productId))->validate($attribute, $value, $fail);
                    }
                },
            ],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }
    public function messages()
    {
        return [
            'discountType.required'=> __('validation.custom.required'),
            // 'orderId.required'=> __('validation.custom.required'),
            'clientId.required' => __('validation.custom.required'),
            'orderItems.required' => __('validation.custom.required'),
            'status.required' => __('validation.custom.required'),

        ];
    }

}

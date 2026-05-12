<?php

namespace App\Http\Requests\Order\Website;

use App\Helpers\ApiResponse;
use App\Rules\ValidStepQuantity;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                       => 'required|string|max:255',
            'note'                       => 'nullable|string|max:500',
            'phone'                      => 'required|string|max:15',
            'countryCode'                => 'required|string|max:5',
            'email'                      => 'required|email|max:255',
            'address'                    => 'required|string|max:255',
            'streetNumber'               => 'nullable|string|max:255',
            'city'                       => 'required|string|max:100',
            'region'                     => 'nullable|string|max:100',
            'orderItems'                 => 'required|array',
            'orderItems.*.productId'     => 'required|integer|exists:products,id',
            'orderItems.*.qty'           => [
                'required',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, \Closure $fail) {
                    preg_match('/orderItems\.(\d+)\.qty/', $attribute, $matches);
                    $productId = $this->input("orderItems.{$matches[1]}.productId");

                    if ($productId) {
                        (new ValidStepQuantity((int) $productId))->validate($attribute, $value, $fail);
                    }
                },
            ],
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }

    public function messages(): array
    {
        return [
            'name.required'                   => __('validation.custom.required'),
            'orderItems.required'             => __('validation.custom.required'),
            'orderItems.array'                => __('validation.custom.required'),
            'orderItems.*.productId.required' => __('validation.custom.required'),
            'orderItems.*.productId.exists'   => __('validation.custom.required'),
            'orderItems.*.qty.required'       => __('validation.custom.required'),
        ];
    }
}

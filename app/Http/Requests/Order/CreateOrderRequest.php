<?php

namespace App\Http\Requests\Order;

use App\Helpers\ApiResponse;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CreateOrderRequest extends FormRequest
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
            'discount' => ['numeric'],
            'discountType' => ['required', new Enum(DiscountType::class)],
            'clientId' => 'required',
            'clientPhoneId' => 'required',
            'clientEmailId' => 'nullable',
            'clientAddressId' => 'required',
            'status' => ['required',new Enum(OrderStatus::class)],
            'orderItems' => 'required|array',
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
            'clientId.required' => __('validation.custom.required'),
            'orderItems.required' => __('validation.custom.required'),
            'status.required' => __('validation.custom.required')
        ];
    }

}

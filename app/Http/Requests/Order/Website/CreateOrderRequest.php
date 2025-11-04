<?php

namespace App\Http\Requests\Order\Website;

use App\Helpers\ApiResponse;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'name' => 'required|string|max:255',
                'note' => 'nullable|string|max:500',
                'phone'=>'required|string|max:15',
                'countryCode' => 'required|string|max:5',
                'email' => 'required|email|max:255',
                'address' => 'required|string|max:255',
                'streetNumber' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'region' => 'nullable|string|max:100',
                // 'discount' => 'nullable|integer|min:0',
                // 'discountType' => 'nullable|integer',
                // 'status' => 'required|integer',
                'orderItems' => 'required|array',
                'orderItems.*.productId' => 'required|integer',
                'orderItems.*.qty' => 'required|integer|min:1',
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
        'name.required' => __('validation.custom.required'),
        'phones.required' => __('validation.custom.required'),
        'phones.array' => __('validation.custom.required'),
        'phones.*.phone.required' => __('validation.custom.required'),
        'phones.*.countryCode.required' => __('validation.custom.required'),

        'emails.required' => __('validation.custom.required'),
        'emails.array' => __('validation.custom.required'),
        'emails.*.email.required' => __('validation.custom.required'),

        'addresses.required' => __('validation.custom.required'),
        'addresses.array' => __('validation.custom.required'),
        'addresses.*.address.required' => __('validation.custom.required'),
        'addresses.*.city.required' => __('validation.custom.required'),

        'orderItems.required' => __('validation.custom.required'),
        'orderItems.array' => __('validation.custom.required'),
        'orderItems.*.productId.required' => __('validation.custom.required'),
        'orderItems.*.qty.required' => __('validation.custom.required'),

        'status.required' => __('validation.custom.required'),
        'clientId.required' => __('validation.custom.required'),
        'discountType.required' => __('validation.custom.required'),
    ];
}
}

<?php

namespace App\Http\Requests\Product;

use App\Helpers\ApiResponse;
use App\Enums\Product\UnitType;
use Illuminate\Validation\Rule;
use App\Enums\Product\ProductStatus;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Product\LimitedQuantity;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
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
    {//categoryId, name, description, price, status
        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('products')->ignore($this->route('product'))],
            'description'   => ['nullable', 'string', 'max:255'],
            // price = unit price (سعر الوحدة الواحدة)
            'price'         => ['required', 'numeric', 'min:0'],
            'status'        => ['required', new Enum(ProductStatus::class)],
            'categoryId'    => ['nullable', 'numeric', 'exists:categories,id'],
            'cost'          => ['nullable', 'numeric', 'min:0'],
            'specifications' => ['nullable', 'array'],
            'isLimitedQuantity' => ['required', new Enum(LimitedQuantity::class)],
            'quantity'      => ['required_if:isLimitedQuantity,' . LimitedQuantity::LIMITED->value, 'nullable', 'numeric', 'min:0'],
            'unitType'      => ['required', new Enum(UnitType::class)],
            'isPromotion'   => ['required', 'in:0,1'],
            'isFreeShipping' => ['required', 'in:0,1'],
            'crossedPrice'  => ['nullable', 'numeric', 'min:0'],
            // Unit fields — default to 1 for piece-based products
            'unitId'        => ['nullable', 'integer', 'exists:units,id'],
            'quantityStep'  => ['nullable', 'numeric', 'min:0.001'],
            'minQuantity'   => ['nullable', 'numeric', 'min:0.001'],
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
            'name.required'=> __('validation.custom.required'),
            'categoryIds.required'=> __('validation.custom.required'),
            'price.required' => __('validation.custom.required'),
            'cost.required' => __('validation.custom.required'),
            'isLimitedQuantity.required' => __('validation.custom.required'),
            'quantity.required' => __('validation.custom.required'),
        ];
    }
}

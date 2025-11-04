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
            // "categoryIds" => ["required"],
            "name" => ["required", "string", "max:255",Rule::unique('products')->ignore($this->route('product'))],
            "description" => ["nullable", "string", "max:255"],
            "price" => ["required"],
            "status" => ["required", new Enum(ProductStatus::class)],
            "categoryId" => [ "nullable","numeric",'exists:categories,id'],
            // "subCategoryId" => [ "nullable","numeric",'exists:categories,id'],
            'cost' => ['nullable'],
            "specifications"=>["nullable","array"],
            "isLimitedQuantity" => ["required", new Enum(LimitedQuantity::class)],
            'quantity' => ['required_if:isLimitedQuantity,' . LimitedQuantity::LIMITED->value],
            'unitType' => ['required', new Enum(UnitType::class)],
            'isPromotion' => ['nullable','in:0,1'],
            'isFreeShipping' => ['nullable','in:0,1'],
            'crossedPrice' => ['nullable','numeric'],

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

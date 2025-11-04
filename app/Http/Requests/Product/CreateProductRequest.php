<?php

namespace App\Http\Requests\Product;

use App\Helpers\ApiResponse;
use App\Enums\Product\UnitType;
use App\Enums\Product\ProductStatus;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Product\LimitedQuantity;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateProductRequest extends FormRequest
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
            "productMedia" => ["nullable", "array"],
            "name" => ["required", "string","unique:products,name"],
            "price" => ["required"],
            "status" => ["required", new Enum(ProductStatus::class)],
            "description" => ["nullable", "string"],
            "categoryId" => [ "nullable","numeric",'exists:categories,id'],
            // "subCategoryId" => [ "nullable","numeric",'exists:categories,id'],
            "specifications"=>["nullable","array"],
            'cost' => ['nullable'],
            "isLimitedQuantity" => ["required", new Enum(LimitedQuantity::class)],
            'quantity' => ['required_if:isLimitedQuantity,' . LimitedQuantity::LIMITED->value],
            'unitType' => ['required', new Enum(UnitType::class)],
            'isPromotion' => ['nullable','in:0,1'],
            'isFreeShipping' => ['nullable','in:0,1'],
            'crossedPrice' => ['nullable','numeric'],
              //crossed_price, is_promotion, is_free_shipping, unit_type
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
            'name.unique'=> __('validation.custom.unique'),
            'price.required' => __('validation.custom.required'),
            'cost.required' => __('validation.custom.required'),
            'isLimitedQuantity.required' => __('validation.custom.required'),
            'quantity.required' => __('validation.custom.required'),
        ];
    }
}

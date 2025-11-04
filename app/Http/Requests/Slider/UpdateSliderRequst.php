<?php

namespace App\Http\Requests\Slider;

use App\Enums\IsActive;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSliderRequst extends FormRequest
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
            "name" => ['required', Rule::unique('sliders')->ignore($this->route('slider'))],
            "isActive"=>['required',new Enum(IsActive::class)],
            // "startDate"=>['nullable','date'],
            // 'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            "sliderItems"=>['required','array'],
            "silderItems.*.productId"=>['required','exists:products,id'],
            "silderItems.*.actionStatus"=>['required',Rule::in(['create','update'])]
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
            'name.unique' => __('validation.custom.unique'),
            'name.required' => __('validation.custom.required'),
            'silderItems.required'=> __('validation.custom.required'),
            'silderItems.*.productId' => __('validation.custom.required'),
            'silderItems.*.actionStatus' => __('validation.custom.required'),

        ];
    }
}

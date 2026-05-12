<?php

namespace App\Http\Requests\Unit;

use App\Helpers\ApiResponse;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('units', 'name')->ignore($this->route('unit'))],
            'step' => ['required', 'numeric', 'min:0.001'],
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
            'name.required' => __('validation.custom.required'),
            'name.unique'   => __('validation.custom.unique'),
            'step.required' => __('validation.custom.required'),
        ];
    }
}

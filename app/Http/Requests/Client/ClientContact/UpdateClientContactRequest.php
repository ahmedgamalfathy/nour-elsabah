<?php

namespace App\Http\Requests\Client\ClientContact;

use App\Enums\IsMain;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class UpdateClientContactRequest extends FormRequest
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
            'phone' => 'required|string|unique:client_phones,phone|max:255',
            'clientId' => 'required|integer',
            'isMain' =>['required',new Enum(IsMain::class)],
            'countryCode' => 'nullable|string|max:10',
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
            'phone.unique' => __('validation.custom.unique'),
            'isMain.required'=> __('validation.custom.required'),
            'clientId.required' => __('validation.custom.required'),
        ];
    }
}

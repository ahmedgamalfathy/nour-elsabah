<?php

namespace App\Http\Requests\Client;

use App\Enums\IsMain;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CreateClientRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
            'phones'=>'nullable|array',
            'phones.*.phone'=>'required|unique:client_phones,phone|max:255',
            'phones.*.isMain'=>['required',new Enum(IsMain::class)],
            'phones.*.countryCode'=>'nullable|string|max:255',
            "email"=>"nullable|email|unique:client_emails,email|max:255",
            // 'emails'=>'nullable|array',
            // 'emails.*.isMain'=>['required',new Enum(IsMain::class)],
            // 'emails.*.email'=>'required|email|unique:client_emails,email|max:255',
            'addresses'=>'nullable|array',
            'addresses.*.address'=>'required|string|max:255',
            'addresses.*.isMain'=>['required',new Enum(IsMain::class)],
            "addresses.*.streetNumber"=>['nullable'],
            "addresses.*.city"=>['nullable'],
            "addresses.*.region"=>['nullable'],
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
            'phones.*.phone.unique' => __('validation.custom.unique'),
            // 'emails.*.email.unique' => __('validation.custom.unique'),
            'email.unique' => __('validation.custom.unique'),
            'email.required' => __('validation.custom.required'),
            'addresses.*.address.unique' => __('validation.custom.unique'),
            'name.required' => __('validation.custom.required')
        ];
    }

}

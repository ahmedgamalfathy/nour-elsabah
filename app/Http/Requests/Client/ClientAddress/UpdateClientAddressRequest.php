<?php

namespace App\Http\Requests\Client\ClientAddress;

use App\Enums\IsMain;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateClientAddressRequest extends FormRequest
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
            'clientId' => 'required',
           'address' => 'required|string|max:255',
            'isMain' => ['required',new Enum(IsMain::class)],
            'streetNumber'=>['nullable','string'] ,
            'city' =>['nullable','string'],
            'region'=>['nullable','string']
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
            'address.unique'=>__('validation.custom.unique'),
            'address.required'=>__('validation.custom.required')
        ];
    }

}

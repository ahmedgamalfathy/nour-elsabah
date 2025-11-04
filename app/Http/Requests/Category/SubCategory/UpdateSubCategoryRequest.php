<?php

namespace App\Http\Requests\Category\SubCategory;

use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;
use App\Enums\Product\CategoryStatus;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class UpdateSubCategoryRequest extends FormRequest
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
           'subCategoryName'=> ['required',
                    Rule::unique('categories', 'name')
                    ->ignore($this->route('sub_category'))
                    ->where(function ($query) {
                        return $query->where('parent_id', $this->input('parentId')); // Only check uniqueness for main categories
                    }),
                ],

            'isActive' => ['required', new Enum(CategoryStatus::class)],
            'subCategoryPath' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2024',
            'parentId' => 'nullable',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }


}

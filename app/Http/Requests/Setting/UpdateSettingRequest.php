<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'القيمة مطلوبة',
            'description.string' => 'الوصف يجب أن يكون نص',
            'description.max' => 'الوصف يجب ألا يتجاوز 500 حرف',
        ];
    }
}

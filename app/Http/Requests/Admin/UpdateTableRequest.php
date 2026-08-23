<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('tables', 'table_number')->ignore($this->route('table')),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

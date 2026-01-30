<?php

namespace App\Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriModulRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'program_id' => ['nullable', 'integer', 'exists:program,id'],
            'jenjang_id' => ['nullable', 'integer', 'exists:jenjang,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

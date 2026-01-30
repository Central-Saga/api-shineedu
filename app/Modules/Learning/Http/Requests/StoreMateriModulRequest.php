<?php

namespace App\Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriModulRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permission handled by middleware
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'program_id' => ['nullable', 'integer', 'exists:program,id'],
            'jenjang_id' => ['nullable', 'integer', 'exists:jenjang,id'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.type' => ['required_with:items', 'string', 'in:VIDEO,PDF,LINK,TEXT,QUIZ,FILE'],
            'items.*.title' => ['required_with:items', 'string', 'max:255'],
            'items.*.content' => ['nullable', 'string'],
            'items.*.url' => ['nullable', 'url'],
            'items.*.file_path' => ['nullable', 'string'],
            'items.*.order_no' => ['nullable', 'integer', 'min:1'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul modul wajib diisi.',
            'items.*.type.in' => 'Tipe item harus salah satu dari: VIDEO, PDF, LINK, TEXT, QUIZ, FILE.',
        ];
    }
}

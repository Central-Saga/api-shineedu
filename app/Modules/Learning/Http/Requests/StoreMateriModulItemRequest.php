<?php

namespace App\Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriModulItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:VIDEO,PDF,LINK,TEXT,QUIZ,FILE'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'file_path' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:102400'], // 100MB max
            'order_no' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Tipe item harus salah satu dari: VIDEO, PDF, LINK, TEXT, QUIZ, FILE.',
            'file.max' => 'Ukuran file maksimal 100MB.',
        ];
    }
}

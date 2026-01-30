<?php

namespace App\Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_text' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:102400'], // 100MB max
            'attachment_path' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.max' => 'Ukuran file maksimal 100MB.',
        ];
    }
}

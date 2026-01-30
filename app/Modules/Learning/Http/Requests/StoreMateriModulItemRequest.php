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
            'type' => ['required', 'string', 'in:FILE,URL'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required_if:type,URL', 'nullable', 'url'],
            'file' => [
                'required_if:type,FILE',
                'nullable',
                'file',
                'max:51200', // 50MB max
                'mimes:doc,docx,xls,xlsx,pdf,ppt,pptx,jpg,jpeg,png,gif,zip'
            ],
            'order_no' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Tipe item harus FILE atau URL.',
            'url.required_if' => 'URL wajib diisi untuk tipe URL.',
            'file.required_if' => 'File wajib diupload untuk tipe FILE.',
            'file.max' => 'Ukuran file maksimal 50MB.',
            'file.mimes' => 'File harus berformat: Word, Excel, PDF, PowerPoint, Gambar (JPG/PNG/GIF), atau ZIP.',
        ];
    }
}

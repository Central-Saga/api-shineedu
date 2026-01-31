<?php

namespace App\Modules\Blog\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBlogAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blog.manage');
    }

    public function rules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validation($validator->errors(), 'Validasi gagal')
        );
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File gambar wajib diunggah.',
            'file.image' => 'File harus berupa gambar.',
            'file.max' => 'Ukuran gambar maksimal 5 MB.',
        ];
    }
}

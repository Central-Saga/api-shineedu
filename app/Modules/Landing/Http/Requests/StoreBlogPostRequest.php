<?php

namespace App\Modules\Landing\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('landing.blog.create');
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:100000',
            'excerpt' => 'nullable|string|max:5000',
            'status' => 'required|in:draft,published',
            'category' => 'nullable|string|max:64',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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
            'title.required' => 'Judul wajib diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'content.max' => 'Konten maksimal 100.000 karakter.',
            'excerpt.max' => 'Deskripsi/ringkasan maksimal 5.000 karakter.',
            'category.max' => 'Kategori maksimal 64 karakter.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus draft atau published.',
            'image.image' => 'File harus berupa gambar (JPG, PNG, GIF, WebP).',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
        ];
    }
}

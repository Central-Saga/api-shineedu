<?php

namespace App\Modules\Landing\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLandingGalleryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('landing.gallery.manage');
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
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
            'image.required' => 'Gambar wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
        ];
    }
}

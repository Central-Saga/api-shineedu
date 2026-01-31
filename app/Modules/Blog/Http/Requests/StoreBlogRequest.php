<?php

namespace App\Modules\Blog\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blog.manage');
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,draft',
            'category' => 'required|in:tips,travel,trips',
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
            'content.required' => 'Konten wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus published atau draft.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori harus tips, travel, atau trips.',
        ];
    }
}

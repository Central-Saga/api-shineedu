<?php

namespace App\Modules\Landing\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('landing.blog.update');
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string|max:100000',
            'excerpt' => 'nullable|string|max:5000',
            'status' => 'sometimes|in:draft,published',
            'category' => 'nullable|string|max:64',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_image' => 'nullable|boolean',
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
            'status.in' => 'Status harus draft atau published.',
        ];
    }
}

<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('catalog.program.update');
    }

    public function rules(): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program', 'kode')->ignore($this->route('program')),
            ],
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'fitur' => 'nullable|array',
            'fitur.*' => 'string|max:255',
            'status' => 'required|in:Aktif,Non Aktif',
            'is_highlight' => 'nullable|boolean',
            'jenjang_ids' => 'nullable|array',
            'jenjang_ids.*' => 'exists:jenjang,id',
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
            'kode.required' => 'Kode wajib diisi.',
            'kode.unique' => 'Kode sudah digunakan, silakan gunakan kode lain.',
            'nama.required' => 'Nama wajib diisi.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus Aktif atau Non Aktif.',
            'jenjang_ids.exists' => 'Salah satu jenjang yang dipilih tidak valid.',
        ];
    }
}

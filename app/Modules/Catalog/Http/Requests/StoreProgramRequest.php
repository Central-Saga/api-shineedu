<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('catalog.program.create');
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|unique:program,kode|max:255',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:Aktif,Non Aktif',
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

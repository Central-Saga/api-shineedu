<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class StoreJenjangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('catalog.jenjang.create');
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|unique:jenjang,kode|max:255',
            'nama' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Non Aktif',
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
        ];
    }
}

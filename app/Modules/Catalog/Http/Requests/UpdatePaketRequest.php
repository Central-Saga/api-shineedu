<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('catalog.paket.update');
    }

    public function rules(): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('paket', 'kode')->ignore($this->route('paket')),
            ],
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string',
            'pertemuan_per_bulan' => 'nullable|integer|min:1',
            'durasi_menit' => 'required|integer|min:1',
            'boleh_mix_mapel' => 'boolean',
            'max_mapel' => 'nullable|integer|min:1',
            'bisa_tambah_pertemuan' => 'boolean',
            'bisa_ganti_hari' => 'boolean',
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
            'tipe.required' => 'Tipe wajib diisi.',
            'durasi_menit.required' => 'Durasi menit wajib diisi.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus Aktif atau Non Aktif.',
        ];
    }
}

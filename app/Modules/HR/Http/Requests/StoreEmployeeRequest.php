<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kode_karyawan' => ['required', 'string', 'max:50', 'unique:karyawan,kode_karyawan'],
            'user_id' => ['required', 'exists:users,id'],
            'kategori_karyawan' => ['required', 'string', 'max:255'],
            'subtipe_kontrak' => ['nullable', 'string', 'max:255'],
            'tipe_gaji' => ['nullable', 'string', 'max:255'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'bank_nama' => ['nullable', 'string', 'max:255'],
            'bank_no_rekening' => ['nullable', 'string', 'max:50'],
            'nomor_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tanggal_lahir' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:aktif,nonaktif'],
        ];
    }
}

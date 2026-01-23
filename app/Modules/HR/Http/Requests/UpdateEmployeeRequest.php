<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
        $employee = $this->route('employee');

        return [
            'kode_karyawan' => ['nullable', 'string', 'max:50', Rule::unique('karyawan', 'kode_karyawan')->ignore($employee)],
            'user_id' => ['nullable', 'exists:users,id'],
            'kategori_karyawan' => ['nullable', 'string', 'max:255'],
            'subtipe_kontrak' => ['nullable', 'string', 'max:255'],
            'tipe_gaji' => ['nullable', 'string', 'max:255'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'bank_nama' => ['nullable', 'string', 'max:255'],
            'bank_no_rekening' => ['nullable', 'string', 'max:50'],
            'nomor_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tanggal_lahir' => ['nullable', 'date'],
            'divisi' => ['nullable', 'string', 'in:Coding,Non-Coding,Operasional'],
            'status' => ['nullable', 'string', 'in:aktif,nonaktif'],
        ];
    }
}

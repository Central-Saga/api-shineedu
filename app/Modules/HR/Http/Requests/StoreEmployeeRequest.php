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
            // Dictionary 'user' validation
            'user' => ['required', 'array'],
            'user.user_id' => ['nullable', 'exists:users,id'],
            'user.user_name' => ['required_without:user.user_id', 'nullable', 'string', 'max:255'],
            'user.user_email' => ['required_without:user.user_id', 'nullable', 'email', 'unique:users,email'],
            'user.user_password' => ['required_without:user.user_id', 'nullable', 'string', 'min:6'],
            'user.user_role' => ['nullable', 'string'],

            // Dictionary 'employee' validation
            'employee' => ['required', 'array'],
            'employee.kode_karyawan' => ['nullable', 'string', 'max:50', 'unique:karyawan,kode_karyawan'],
            'employee.kategori_karyawan' => ['required', 'string', 'max:255'],
            'employee.subtipe_kontrak' => ['nullable', 'string', 'max:255'],
            'employee.tipe_gaji' => ['nullable', 'string', 'max:255'],
            'employee.gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'employee.bank_nama' => ['nullable', 'string', 'max:255'],
            'employee.bank_no_rekening' => ['nullable', 'string', 'max:50'],
            'employee.nomor_hp' => ['nullable', 'string', 'max:30'],
            'employee.alamat' => ['nullable', 'string', 'max:500'],
            'employee.tanggal_lahir' => ['nullable', 'date'],
            'employee.divisi' => ['nullable', 'string', 'in:Coding,Non-Coding,Operasional', 'nullable'],
            'employee.status' => ['required', 'string', 'in:aktif,nonaktif'],
        ];
    }
}

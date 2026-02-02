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

            // Link to existing user (Optional now)
            'user_id' => ['nullable', 'exists:users,id'],

            // If user_id is null, these are required to create a NEW user
            'user_name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'user_email' => ['required_without:user_id', 'nullable', 'email', 'unique:users,email'],
            'user_password' => ['required_without:user_id', 'nullable', 'string', 'min:6'],
            'user_role' => ['nullable', 'string'], // e.g. "Teacher", "Admin"

            // Employee Data
            'kategori_karyawan' => ['required', 'string', 'max:255'],
            'subtipe_kontrak' => ['nullable', 'string', 'max:255'],
            'tipe_gaji' => ['nullable', 'string', 'max:255'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'bank_nama' => ['nullable', 'string', 'max:255'],
            'bank_no_rekening' => ['nullable', 'string', 'max:50'],
            'nomor_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tanggal_lahir' => ['nullable', 'date'],
            'divisi' => ['nullable', 'string', 'in:Coding,Non-Coding,Operasional'],
            'status' => ['required', 'string', 'in:aktif,nonaktif'],
        ];
    }
}

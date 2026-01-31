<?php

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMuridRequest extends FormRequest
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
            'kode_murid' => ['nullable', 'string', 'max:50'],
            'nama_lengkap' => ['sometimes', 'required', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tanggal_lahir' => ['nullable', 'date'],
            'no_hp' => ['sometimes', 'required', 'string', 'min:8', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],

            'jenjang_id' => ['nullable', 'exists:jenjang,id'],
            'sekolah_asal' => ['nullable', 'string', 'max:255'],
            'kelas_sekolah' => ['nullable', 'string', 'max:50'],

            'nama_wali' => ['nullable', 'string', 'max:255'],
            'no_hp_wali' => ['nullable', 'string', 'max:20'],
            'email_wali' => ['nullable', 'email', 'max:255'],
            'hubungan_wali' => ['nullable', 'string', 'max:50'],

            'catatan_khusus' => ['nullable', 'string'],
            'kebutuhan_khusus' => ['nullable', 'string'],

            'status' => ['nullable', 'in:Aktif,Non Aktif'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}

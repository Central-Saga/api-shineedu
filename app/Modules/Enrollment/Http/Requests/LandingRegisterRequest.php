<?php

namespace App\Modules\Enrollment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandingRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'murid_baru' => ['required', 'array'],
            'murid_baru.nama_lengkap' => ['required', 'string', 'max:255'],
            'murid_baru.no_hp' => ['required', 'string', 'max:20'],
            'murid_baru.email' => ['required', 'email', 'max:255'],
            'murid_baru.jenis_kelamin' => ['nullable', 'in:L,P'],
            'murid_baru.tanggal_lahir' => ['nullable', 'date'],
            'murid_baru.alamat' => ['nullable', 'string'],
            'murid_baru.nama_wali' => ['nullable', 'string', 'max:255'],
            'murid_baru.no_hp_wali' => ['nullable', 'string', 'max:20'],
            'murid_baru.email_wali' => ['nullable', 'email', 'max:255'],
            'murid_baru.hubungan_wali' => ['nullable', 'string', 'max:50'],

            'program_id' => ['required', 'exists:program,id'],
            'jenjang_id' => ['required', 'exists:jenjang,id'],
            'paket_id' => ['required', 'exists:paket,id'],
            'jumlah_siswa' => ['required', 'integer', 'min:1'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan' => ['nullable', 'string'],
            'biaya_pendaftaran_amount' => ['nullable', 'numeric', 'min:0'],
            'biaya_pendaftaran_status' => ['nullable', 'string', 'in:UNPAID,PAID,WAIVED'],
            'biaya_pendaftaran_due_date' => ['nullable', 'date'],
        ];
    }
}

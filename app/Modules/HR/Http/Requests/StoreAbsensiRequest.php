<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'karyawan_id' => 'required|exists:karyawan,id',
            'status_kehadiran' => 'required|string|in:hadir,alpa,izin,sakit',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i|after:jam_masuk',
            'sumber_absen' => 'nullable|string',
            'catatan' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->status_kehadiran === 'hadir') {
                if (! $this->jam_masuk || ! $this->jam_pulang) {
                    $validator->errors()->add('jam_masuk', 'Jam masuk dan pulang wajib diisi jika hadir.');
                }
            }
        });
    }
}

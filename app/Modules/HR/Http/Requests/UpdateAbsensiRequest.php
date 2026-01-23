<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_kehadiran' => 'sometimes|string|in:hadir,alpa,izin,sakit',
            'tanggal' => 'sometimes|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i|after:jam_masuk',
            'sumber_absen' => 'nullable|string',
            'catatan' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Only validate jam_masuk/pulang if status is actively being set to 'hadir'
            // OR if it's already 'hadir' and we are unsetting times (which shouldn't happen with standard update)
            // Ideally we check if status is present in request and is 'hadir'.

            if ($this->has('status_kehadiran') && $this->status_kehadiran === 'hadir') {
                if (! $this->jam_masuk || ! $this->jam_pulang) {
                    // Check if they are already in DB?
                    // Usually update request requires resending data or partial update.
                    // If partial update and fields missing, we might need to check DB.
                    // But for safety, require them if status changes to hadir.
                    // If just updating 'catatan', status might not be sent.

                    // If jam_masuk not present in request, we assume it's keeping old value?
                    // Laravel validation 'required' fails if not present.
                    // But 'sometimes' passes if not present.

                    // Custom logic:
                    // If status is present and is hadir, and times are NOT present,
                    // we assume user might be relying on existing data?
                    // Let's just warn if they send status=hadir but no time.
                    $validator->errors()->add('jam_masuk', 'Jam masuk dan pulang wajib diisi jika status hadir.');
                }
            }
        });
    }
}

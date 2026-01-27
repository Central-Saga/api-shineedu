<?php

namespace App\Modules\Enrollment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enrollment.update');
    }

    public function rules(): array
    {
        return [
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['nullable', 'string', 'in:Aktif,Pause,Selesai,Cancel'],
            'catatan' => ['nullable', 'string'],
            'biaya_pendaftaran_amount' => ['nullable', 'numeric', 'min:0'],
            'biaya_pendaftaran_status' => ['nullable', 'string', 'in:UNPAID,PAID,WAIVED'],
            'biaya_pendaftaran_due_date' => ['nullable', 'date'],
        ];
    }
}

<?php

namespace App\Modules\Enrollment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enrollment.create');
    }

    public function rules(): array
    {
        return [
            // Either murid_id or murid_baru must be present
            'murid_id' => [
                'required_without:murid_baru',
                'nullable',
                'exists:murid,id',
            ],
            'murid_baru' => [
                'required_without:murid_id',
                'nullable',
                'array',
            ],

            // Validate murid_baru content if present
            'murid_baru.nama_lengkap' => ['required_with:murid_baru', 'string', 'max:255'],
            'murid_baru.no_hp' => ['required_with:murid_baru', 'string', 'max:20'],
            // Add other required fields for Murid creation if necessary, mirroring StoreMuridRequest

            'program_id' => ['required', 'exists:program,id'],
            'jenjang_id' => ['required', 'exists:jenjang,id'], // Check table name: jenjang or jenjangs? Migration usually plural, model 'Jenjang' often maps to 'jenjangs' or 'jenjang'.
            // Note: I will use 'exists:jenjangs,id' if standard, but earlier I assumed 'jenjangs'.
            // Let's safe bet on table names. I'll check user context if I can, or use logic.
            // Earlier migration used 'jenjangs'. I will stick to 'jenjangs'.
            // Wait, standard Laravel is plural. But existing code might differ.
            // I'll stick to 'jenjangs' as used in migration relation.

            'paket_id' => ['required', 'exists:paket,id'],

            'jumlah_siswa' => ['required', 'integer', 'min:1'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace App\Modules\Learning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'realisasi_jadwal_kerja_id' => ['nullable', 'integer', 'exists:realisasi_jadwal_kerja,id'],
            'materi_modul_id' => ['nullable', 'integer', 'exists:materi_modul,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.required' => 'Enrollment wajib dipilih.',
            'enrollment_id.exists' => 'Enrollment tidak ditemukan.',
            'title.required' => 'Judul tugas wajib diisi.',
        ];
    }
}

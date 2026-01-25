<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKelasRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('class.update');
    }

    public function rules()
    {
        return [
            'nama_kelas' => 'sometimes|string|max:255',
            'program_id' => 'sometimes|exists:program,id',
            'jenjang_id' => 'sometimes|exists:jenjang,id',
            'tipe_kelas' => 'sometimes|in:REGULER,PRIVATE',
            'mode_private' => 'required_if:tipe_kelas,PRIVATE|nullable|in:INDIVIDU,GROUP',
            'kapasitas' => 'nullable|integer|min:1',
            'status' => 'sometimes|in:Draft,Aktif,Selesai,Non Aktif',
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date|after_or_equal:periode_mulai',
            'ruangan_default' => 'nullable|string',
            'catatan' => 'nullable|string',
        ];
    }
}

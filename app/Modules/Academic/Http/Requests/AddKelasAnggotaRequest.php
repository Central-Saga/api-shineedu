<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddKelasAnggotaRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('class.manage_members');
    }

    public function rules()
    {
        return [
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'exists:enrollments,id',
            'tanggal_masuk' => 'nullable|date',
        ];
    }
}

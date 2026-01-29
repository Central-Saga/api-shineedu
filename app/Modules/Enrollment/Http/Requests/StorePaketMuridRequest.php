<?php

namespace App\Modules\Enrollment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaketMuridRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Middleware handles auth
    }

    public function rules()
    {
        return [
            'paket_id' => 'required|exists:paket,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'catatan' => 'nullable|string',
        ];
    }
}

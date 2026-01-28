<?php

namespace App\Modules\Enrollment\Http\Requests;

use App\Modules\Enrollment\Domain\Models\PaketMuridLedger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustPaketMuridRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Middleware handles auth
    }

    public function rules()
    {
        return [
            'type' => ['required', Rule::in([PaketMuridLedger::TYPE_ADJUST, PaketMuridLedger::TYPE_EXPIRE])],
            'qty' => 'required|integer', // Can be positive or negative
            'reason' => 'required|string|max:255',
        ];
    }
}

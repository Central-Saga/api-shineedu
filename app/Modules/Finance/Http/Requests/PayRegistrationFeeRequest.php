<?php

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Domain\Models\KasTransaksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayRegistrationFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0'],
            'metode' => ['required', Rule::in([
                KasTransaksi::METODE_CASH,
                KasTransaksi::METODE_TRANSFER,
                KasTransaksi::METODE_QRIS,
                KasTransaksi::METODE_E_WALLET,
                KasTransaksi::METODE_OTHER,
            ])],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'external_ref' => ['nullable', 'string', 'max:100'],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.gt' => 'Jumlah pembayaran harus lebih dari 0.',
            'metode.required' => 'Metode pembayaran wajib diisi.',
        ];
    }
}

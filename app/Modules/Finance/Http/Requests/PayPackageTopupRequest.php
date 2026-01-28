<?php

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Domain\Models\KasTransaksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayPackageTopupRequest extends FormRequest
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
            'paket_murid_id' => ['nullable', 'integer', 'exists:paket_murid,id'],
            'paket_id' => ['nullable', 'integer', 'exists:paket,id'],
            'topup_qty' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'catatan' => ['nullable', 'string', 'max:1000'],
            'external_ref' => ['nullable', 'string', 'max:100'],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
            'bukti_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->paket_murid_id) && empty($this->paket_id)) {
                $validator->errors()->add('paket', 'Harus mengisi paket_murid_id atau paket_id.');
            }
        });
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
            'topup_qty.min' => 'Jumlah topup minimal 1.',
            'topup_qty.max' => 'Jumlah topup maksimal 100.',
        ];
    }
}

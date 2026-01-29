<?php

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Domain\Models\KasTransaksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKasTransaksiRequest extends FormRequest
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
            'type' => ['required', Rule::in([KasTransaksi::TYPE_IN, KasTransaksi::TYPE_OUT])],
            'tanggal' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'metode' => ['required', Rule::in([
                KasTransaksi::METODE_CASH,
                KasTransaksi::METODE_TRANSFER,
                KasTransaksi::METODE_QRIS,
                KasTransaksi::METODE_E_WALLET,
                KasTransaksi::METODE_OTHER,
            ])],
            'kategori' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'pihak' => ['nullable', 'string', 'max:150'],
            'external_ref' => ['nullable', 'string', 'max:100'],
            'idempotency_key' => ['nullable', 'string', 'max:80'],
            'bukti_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Tipe transaksi wajib diisi.',
            'type.in' => 'Tipe transaksi harus IN atau OUT.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.gt' => 'Jumlah harus lebih dari 0.',
            'metode.required' => 'Metode pembayaran wajib diisi.',
            'kategori.required' => 'Kategori wajib diisi.',
        ];
    }
}

<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Catalog\Application\Services\PricingService;

class UpdatePaketHargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('catalog.pricing.update');
    }

    public function rules(): array
    {
        return [
            'program_id' => 'required|exists:program,id',
            'jenjang_id' => 'required|exists:jenjang,id',
            'paket_id' => 'required|exists:paket,id',
            'min_siswa' => 'required|integer|min:1',
            'max_siswa' => 'required|integer|gte:min_siswa',
            'harga' => 'required|numeric|min:0',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'status' => 'required|in:Aktif,Non Aktif',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $service = app(PricingService::class);
            // $this->route('harga') might return ID or Model depending on binding.
            // Assuming route binding will be 'harga' => PacketHarga model or ID.
            $model = $this->route('harga');
            $id = $model instanceof \App\Modules\Catalog\Domain\Models\PaketHarga ? $model->id : $model;

            if ($service->checkOverlap($this->validated(), $id)) {
                $validator->errors()->add('base', 'Pricing range overlaps with an existing active rule.');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validation($validator->errors(), 'Validasi gagal')
        );
    }

    public function messages(): array
    {
        return [
            'program_id.required' => 'Program wajib dipilih.',
            'jenjang_id.required' => 'Jenjang wajib dipilih.',
            'paket_id.required' => 'Paket wajib dipilih.',
            'min_siswa.required' => 'Minimal siswa wajib diisi.',
            'max_siswa.required' => 'Maksimal siswa wajib diisi.',
            'max_siswa.gte' => 'Maksimal siswa harus lebih besar atau sama dengan minimal siswa.',
            'harga.required' => 'Harga wajib diisi.',
            'effective_to.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai.',
            'status.required' => 'Status wajib diisi.',
        ];
    }
}

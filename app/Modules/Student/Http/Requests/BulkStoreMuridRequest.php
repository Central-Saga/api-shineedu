<?php

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreMuridRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorized by middleware/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:1000'],
            'items.*' => ['required', 'array'],
            // Reuse same rules as StoreMuridRequest for each item
            'items.*.nama_lengkap' => ['required', 'string', 'max:255'],
            'items.*.jenis_kelamin' => ['nullable', 'in:L,P'],
            'items.*.tanggal_lahir' => ['nullable', 'date_format:Y-m-d'],
            'items.*.no_hp' => ['nullable', 'string', 'min:8', 'max:20'],
            'items.*.email' => ['nullable', 'email', 'max:255'],
            'items.*.alamat' => ['nullable', 'string'],
            'items.*.status' => ['nullable', 'in:Aktif,Non Aktif'],
            'items.*.password' => ['nullable', 'string', 'min:8'],
            'dry_run' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'items.*.nama_lengkap' => 'nama lengkap',
            'items.*.jenis_kelamin' => 'jenis kelamin',
            'items.*.tanggal_lahir' => 'tanggal lahir',
            'items.*.no_hp' => 'nomor HP',
            'items.*.email' => 'email',
            'items.*.alamat' => 'alamat',
            'items.*.status' => 'status',
            'items.*.password' => 'password',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'dry_run' => $this->boolean('dry_run', false),
        ]);
    }
}

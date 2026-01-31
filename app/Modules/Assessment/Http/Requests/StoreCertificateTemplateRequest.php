<?php

namespace App\Modules\Assessment\Http\Requests;

use App\Modules\Assessment\Domain\Enums\CertificateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCertificateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handle permission via middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(CertificateType::class)],
            // Strict Validation: Must provide cover mapping with at least student_name and date
            'data_mapping' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail("The $attribute must be a valid JSON string.");
                            return;
                        }
                        $value = $decoded;
                    }
                    if (!is_array($value)) {
                        $fail("The $attribute must be a valid array or JSON object.");
                        return;
                    }
                    if (!isset($value['cover'])) {
                        $fail("The $attribute must contain a 'cover' key.");
                    }
                }
            ],
            'cover_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'result_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('data_mapping') && is_string($this->data_mapping)) {
            $this->merge([
                'data_mapping' => json_decode($this->data_mapping, true)
            ]);
        }
    }
}

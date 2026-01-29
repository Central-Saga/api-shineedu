<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'posted_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:1000',
            'responsibilities' => 'nullable|array',
            'responsibilities.*' => 'string|max:1000',
            'benefits' => 'nullable|array',
            'benefits.*' => 'string|max:1000',
            'is_active' => 'nullable|boolean',
        ];
    }
}

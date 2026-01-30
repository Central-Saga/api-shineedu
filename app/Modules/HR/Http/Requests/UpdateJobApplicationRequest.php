<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:50',
            'position_id' => 'sometimes|exists:job_vacancies,id',
            'job_vacancy_id' => 'sometimes|exists:job_vacancies,id',
            'experience' => 'sometimes|string|in:fresh-graduate,1-2,3-5,5-10,10+',
            'education' => 'sometimes|string|in:sma,d3,s1,s2,s3',
            'address' => 'sometimes|string',
            'status' => 'sometimes|string|in:pending,reviewed,shortlisted,rejected,hired',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'cover_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
    }

    protected function prepareForValidation(): void
    {
        $positionId = $this->input('position_id') ?? $this->input('job_vacancy_id');
        if ($positionId !== null) {
            $this->merge(['job_vacancy_id' => $positionId]);
        }
    }
}

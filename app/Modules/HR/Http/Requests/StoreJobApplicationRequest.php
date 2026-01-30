<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:50',
            'position_id' => 'required_without:job_vacancy_id|exists:job_vacancies,id',
            'job_vacancy_id' => 'required_without:position_id|exists:job_vacancies,id',
            'experience' => 'required|string|in:fresh-graduate,1-2,3-5,5-10,10+',
            'education' => 'required|string|in:sma,d3,s1,s2,s3',
            'address' => 'required|string',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120',
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

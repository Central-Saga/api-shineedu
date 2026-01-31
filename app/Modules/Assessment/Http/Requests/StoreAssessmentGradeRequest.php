<?php

namespace App\Modules\Assessment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => 'required|exists:enrollments,id',
            'certificate_template_id' => 'required|exists:assessment_certificate_templates,id',
            'teacher_karyawan_id' => 'nullable|exists:karyawan,id',
            'scores' => 'required|array',
            'scores.*' => 'numeric|min:0|max:100', // Validate score values
        ];
    }
}

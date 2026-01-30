<?php

namespace App\Modules\Learning\Http\Requests;

use App\Modules\Learning\Domain\Models\AssignmentSubmission;
use Illuminate\Foundation\Http\FormRequest;

class ReviewSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:' . AssignmentSubmission::STATUS_ACCEPTED . ',' . AssignmentSubmission::STATUS_REVISION_REQUESTED],
            'feedback' => ['nullable', 'string'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status review wajib dipilih.',
            'status.in' => 'Status harus ACCEPTED atau REVISION_REQUESTED.',
            'score.max' => 'Nilai maksimal 100.',
        ];
    }
}

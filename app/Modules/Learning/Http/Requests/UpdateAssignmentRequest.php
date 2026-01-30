<?php

namespace App\Modules\Learning\Http\Requests;

use App\Modules\Learning\Domain\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:' . implode(',', Assignment::getStatuses())],
            'materi_modul_id' => ['nullable', 'integer', 'exists:materi_modul,id'],
        ];
    }
}

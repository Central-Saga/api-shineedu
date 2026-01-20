<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
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
        $roleId = $this->route('role')->id ?? null;

        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', 'exists:permissions,name'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama role wajib diisi.',
            'name.max' => 'Nama role maksimal 50 karakter.',
            'name.unique' => 'Nama role sudah digunakan.',
            'permissions.array' => 'Permissions harus berupa array.',
            'permissions.*.required' => 'Permission wajib diisi.',
            'permissions.*.exists' => 'Permission tidak ditemukan.',
        ];
    }
}

<?php

namespace App\Imports;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Domain\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $status = UserStatus::tryFrom($row['status'] ?? '') ?? UserStatus::AKTIF;

        $user = User::create([
            'name'     => $row['name'],
            'email'    => $row['email'],
            'password' => Hash::make($row['password'] ?? 'ShineEdu123!'),
            'status'   => $status,
        ]);

        if (!empty($row['role'])) {
            $user->assignRole($row['role']);
        }

        return $user;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'nullable|exists:roles,name',
            'status'   => ['nullable', Rule::in(UserStatus::values())],
            'password' => 'nullable|string|min:6',
        ];
    }
}

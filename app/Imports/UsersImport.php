<?php

namespace App\Imports;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Identity\Domain\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class UsersImport implements OnEachRow, WithHeadingRow
{
    protected $updateExisting;
    protected $report = [
        'total_rows' => 0,
        'inserted_count' => 0,
        'updated_count' => 0,
        'skipped_count' => 0,
        'validation_errors' => [],
    ];

    public function __construct(bool $updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();
        $this->report['total_rows']++;

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'status' => 'required|string',
            'role' => 'required|string',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            $this->report['validation_errors'][] = "Row {$this->report['total_rows']}: " . implode(', ', $validator->errors()->all());
            return;
        }

        $email = trim($data['email']);
        $user = User::where('email', $email)->first();

        $status = $this->normalizeStatus($data['status'] ?? '');
        $roleName = $this->normalizeRole($data['role'] ?? '');

        if ($user) {
            if ($this->updateExisting) {
                $user->update([
                    'name' => $data['name'],
                    'status' => $status,
                ]);

                if (!empty($data['password'])) {
                    $user->update(['password' => Hash::make($data['password'])]);
                }

                $this->assignRole($user, $roleName);
                $this->report['updated_count']++;
            } else {
                $this->report['skipped_count']++;
            }
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make($data['password'] ?? 'ShineEdu123!'),
                'status' => $status,
            ]);
            $this->assignRole($user, $roleName);
            $this->report['inserted_count']++;
        }
    }

    protected function normalizeStatus($status): UserStatus
    {
        $status = trim((string) $status);
        if (stripos($status, 'Aktif') !== false && stripos($status, 'Non') === false) return UserStatus::AKTIF;
        if (stripos($status, 'Non') !== false) return UserStatus::NON_AKTIF;

        return UserStatus::tryFrom($status) ?? UserStatus::AKTIF;
    }

    protected function normalizeRole($roleName): string
    {
        $roleName = trim((string) $roleName);
        if (strcasecmp($roleName, 'Super Admin') === 0) {
            return 'Superadmin';
        }
        return $roleName;
    }

    protected function assignRole(User $user, string $roleName)
    {
        if (empty($roleName)) return;

        // Ensure role exists before syncing
        $roleExists = \Spatie\Permission\Models\Role::where('name', $roleName)->exists();
        if ($roleExists) {
            $user->syncRoles([$roleName]);
        } else {
            $this->report['validation_errors'][] = "Row {$this->report['total_rows']}: Role '{$roleName}' does not exist.";
        }
    }

    public function getReport(): array
    {
        return $this->report;
    }
}

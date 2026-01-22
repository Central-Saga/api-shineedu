<?php

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserImportService
{
    /**
     * Import users from CSV file.
     *
     * @param string $filePath
     * @param bool $updateExisting
     * @param bool $forcePassword
     * @return array
     */
    public function import(string $filePath, bool $updateExisting = false, bool $forcePassword = false): array
    {
        $report = [
            'total_rows' => 0,
            'inserted_count' => 0,
            'updated_count' => 0,
            'skipped_count' => 0,
            'validation_errors' => [],
            'logs' => [],
        ];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $report['validation_errors'][] = "File not found or not readable: {$filePath}";
            return $report;
        }

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            $report['validation_errors'][] = "Empty CSV or invalid header.";
            fclose($handle);
            return $report;
        }

        // Normalize header
        $header = array_map('trim', $header);

        while (($row = fgetcsv($handle)) !== false) {
            $report['total_rows']++;
            $data = array_combine($header, $row);

            $validator = $this->validateRow($data);

            if ($validator->fails()) {
                $errorMsg = "Row {$report['total_rows']} validation failed: " . implode(', ', $validator->errors()->all());
                $report['validation_errors'][] = $errorMsg;
                $report['logs'][] = $errorMsg;
                continue;
            }

            $email = trim($data['email']);
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                if ($updateExisting) {
                    $this->updateUser($existingUser, $data, $forcePassword);
                    $report['updated_count']++;
                    $report['logs'][] = "Updated existing user: {$email}";
                } else {
                    $report['skipped_count']++;
                    $report['logs'][] = "Skipped existing user: {$email}";
                }
            } else {
                $this->createUser($data);
                $report['inserted_count']++;
                $report['logs'][] = "Inserted new user: {$email}";
            }
        }

        fclose($handle);

        return $report;
    }

    /**
     * Validate a single row of CSV data.
     */
    protected function validateRow(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:6',
            'status' => 'required|string',
            'role' => 'required|string',
        ];

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user record.
     */
    protected function createUser(array $data): User
    {
        $status = $this->normalizeStatus($data['status']);

        $user = User::create([
            'name' => $data['name'],
            'email' => trim($data['email']),
            'password' => Hash::make($data['password'] ?? 'ShineEdu123!'),
            'status' => $status,
        ]);

        $this->assignRole($user, $data['role']);

        return $user;
    }

    /**
     * Update an existing user record.
     */
    protected function updateUser(User $user, array $data, bool $forcePassword): void
    {
        $status = $this->normalizeStatus($data['status']);

        $updateData = [
            'name' => $data['name'],
            'status' => $status,
        ];

        if ($forcePassword && !empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        $this->assignRole($user, $data['role']);
    }

    /**
     * Normalize status string to UserStatus enum value.
     */
    protected function normalizeStatus(string $status): UserStatus
    {
        // Handle "Aktif", "Non Aktif", or raw enum values
        $status = trim($status);

        return UserStatus::tryFrom($status) ?? UserStatus::AKTIF;
    }

    /**
     * Normalize and assign role to user.
     */
    protected function assignRole(User $user, string $roleName): void
    {
        $roleName = trim($roleName);

        // Map "Super Admin" to "Superadmin"
        if (strcasecmp($roleName, 'Super Admin') === 0) {
            $roleName = 'Superadmin';
        }

        // Ensure role exists before syncing
        $roleExists = \Spatie\Permission\Models\Role::where('name', $roleName)->exists();
        if ($roleExists) {
            $user->syncRoles([$roleName]);
        }
    }
}

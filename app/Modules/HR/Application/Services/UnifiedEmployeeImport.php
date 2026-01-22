<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\HR\Models\Employee;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Domain\Enums\UserStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;
use Spatie\Permission\Models\Role;

class UnifiedEmployeeImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    protected array $config;
    protected array $results = [
        'total_rows' => 0,
        'users_inserted' => 0,
        'users_updated' => 0,
        'employees_inserted' => 0,
        'employees_updated' => 0,
        'rows_skipped' => 0,
        'errors' => [],
        'logs' => [],
    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $this->results['total_rows']++;
        $data = $row->toArray();

        // 1. Normalize
        $normalizedData = $this->normalizeRow($data);

        // 2. Validate
        $validator = $this->validateRow($normalizedData);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $errorMsgs = [];
            foreach ($errors as $field => $messages) {
                $errorMsgs[] = "{$field}: " . implode(', ', $messages);
            }

            $msg = "Row {$rowIndex} validation failed: " . implode('; ', $errorMsgs);

            // Debug: append keys if everything seems missing
            if (count($errors) >= 3) {
                $keys = implode(', ', array_keys($data));
                $msg .= " (CSV Keys: {$keys})";
            }

            $this->results['errors'][] = [
                'row' => $rowIndex,
                'message' => $msg
            ];
            $this->results['rows_skipped']++;
            $this->results['logs'][] = "Skipped row {$rowIndex}: Validation Error";
            return;
        }

        // 3. Process Row in Transaction
        DB::beginTransaction();
        try {
            // Upsert User
            $userResult = $this->upsertUserAndRole($normalizedData);
            if ($userResult['status'] === 'inserted') {
                $this->results['users_inserted']++;
            } elseif ($userResult['status'] === 'updated') {
                $this->results['users_updated']++;
            }

            // Upsert Employee
            $employeeResult = $this->upsertEmployee($normalizedData, $userResult['user']->id);
            if ($employeeResult === 'inserted') {
                $this->results['employees_inserted']++;
            } elseif ($employeeResult === 'updated') {
                $this->results['employees_updated']++;
            }

            DB::commit();

            // Success Log
            $this->results['logs'][] = ucfirst($userResult['status']) . " user + " . $employeeResult . " employee: {$normalizedData['email']}/{$normalizedData['kode_karyawan']}";
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "Row {$rowIndex} DB Error: " . $e->getMessage();
            $this->results['errors'][] = [
                'row' => $rowIndex,
                'message' => $msg
            ];
            $this->results['rows_skipped']++;
            $this->results['logs'][] = "Skipped row {$rowIndex}: DB Exception";
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function chunkSize(): int
    {
        return 100; // Chunk size for memory safety
    }

    protected function normalizeRow(array $data): array
    {
        // Debug/Self-correcting key access
        $get = function ($baseKey) use ($data) {
            if (isset($data[$baseKey])) return $data[$baseKey];
            // Try common variations if slugification differs
            $slugged = str_replace('_', '', $baseKey); // kodekaryawan
            if (isset($data[$slugged])) return $data[$slugged];
            $hyphenated = str_replace('_', '-', $baseKey); // kode-karyawan
            if (isset($data[$hyphenated])) return $data[$hyphenated];
            return null;
        };

        return [
            'kode_karyawan'     => trim($get('kode_karyawan') ?? ''),
            'nama'              => trim($get('nama') ?? ''),
            'email'             => trim(strtolower($get('email') ?? '')),
            'password'          => $get('password'),
            'role'              => trim($get('role') ?? ''),
            'kategori_karyawan' => trim($get('kategori_karyawan') ?? ''),
            'subtipe_kontrak'   => !empty($get('subtipe_kontrak')) ? trim($get('subtipe_kontrak')) : null,
            'tipe_gaji'         => !empty($get('tipe_gaji')) ? trim($get('tipe_gaji')) : null,
            'gaji_pokok'        => !empty($get('gaji_pokok')) ? (float) preg_replace('/[^0-9.]/', '', $get('gaji_pokok')) : 0,
            'bank_nama'         => !empty($get('bank_nama')) ? trim($get('bank_nama')) : null,
            'bank_no_rekening'  => !empty($get('bank_no_rekening')) ? trim($get('bank_no_rekening')) : null,
            'nomor_hp'          => !empty($get('nomor_hp')) ? trim($get('nomor_hp')) : null,
            'alamat'            => !empty($get('alamat')) ? trim($get('alamat')) : null,
            'tanggal_lahir'     => !empty($get('tanggal_lahir')) ? $this->parseDate($get('tanggal_lahir')) : null,
            'status'            => strtolower(trim($get('status') ?? 'aktif')),
        ];
    }

    protected function parseDate($date): ?string
    {
        if (empty($date)) return null;
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function validateRow(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'kode_karyawan' => 'required',
            'nama'          => 'required',
            'email'         => 'required|email',
            'status'        => 'required|in:aktif,nonaktif',
            'role'          => ['required', 'string', function ($attribute, $value, $fail) {
                $normalized = ($value === 'Super Admin') ? 'Superadmin' : $value;
                if (!Role::where('name', $normalized)->exists()) {
                    $fail("The role '{$value}' does not exist in the system.");
                }
            }],
        ];

        $attributes = [
            'kode_karyawan' => 'Kode Karyawan',
            'nama'          => 'Nama',
            'email'         => 'Email',
            'status'        => 'Status',
            'role'          => 'Role',
        ];

        return Validator::make($data, $rules, [], $attributes);
    }

    protected function upsertUserAndRole(array $data): array
    {
        $user = User::where('email', $data['email'])->first();
        $status = 'skipped';
        $userStatus = ($data['status'] === 'aktif') ? UserStatus::AKTIF : UserStatus::NON_AKTIF;
        $roleName = ($data['role'] === 'Super Admin') ? 'Superadmin' : $data['role'];

        if (!$user) {
            $user = User::create([
                'name'     => $data['nama'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password'] ?? 'ShineEdu123!'),
                'status'   => $userStatus,
            ]);
            $status = 'inserted';
        } else {
            if ($this->config['user_mode'] === 'update') {
                $updateData = [
                    'name'   => $data['nama'],
                    'status' => $userStatus,
                ];
                if ($this->config['overwrite_password'] && !empty($data['password'])) {
                    $updateData['password'] = Hash::make($data['password']);
                }
                $user->update($updateData);
                $status = 'updated';
            }
        }

        // Assign/Sync Role
        $user->syncRoles([$roleName]);

        return ['user' => $user, 'status' => $status];
    }

    protected function upsertEmployee(array $data, int $userId): string
    {
        $employee = Employee::where('kode_karyawan', $data['kode_karyawan'])->first();
        $status = 'skipped';

        $employeeData = [
            'user_id'           => $userId,
            'kategori_karyawan' => $data['kategori_karyawan'],
            'subtipe_kontrak'   => $data['subtipe_kontrak'],
            'tipe_gaji'         => $data['tipe_gaji'],
            'gaji_pokok'        => $data['gaji_pokok'],
            'bank_nama'         => $data['bank_nama'],
            'bank_no_rekening'  => $data['bank_no_rekening'],
            'nomor_hp'          => $data['nomor_hp'],
            'alamat'            => $data['alamat'],
            'tanggal_lahir'     => $data['tanggal_lahir'],
            'status'            => $data['status'],
        ];

        if (!$employee) {
            Employee::create(array_merge(['kode_karyawan' => $data['kode_karyawan']], $employeeData));
            $status = 'inserted';
        } else {
            if ($this->config['employee_mode'] === 'update') {
                $employee->update($employeeData);
                $status = 'updated';
            }
        }

        return $status;
    }
}

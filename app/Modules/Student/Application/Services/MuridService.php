<?php

namespace App\Modules\Student\Application\Services;

use App\Modules\Student\Domain\Models\Murid;
use Illuminate\Pagination\LengthAwarePaginator;

class MuridService
{
    /**
     * Get list of Murid with filters and pagination.
     */
    public function getList(array $params): LengthAwarePaginator
    {
        $query = Murid::query()->with(['jenjang']);

        // Search
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', "%{$keyword}%")
                    ->orWhere('kode_murid', 'like', "%{$keyword}%")
                    ->orWhere('no_hp', 'like', "%{$keyword}%");
            });
        }

        // Filters
        if (! empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }

        if (! empty($params['jenjang_id'] ?? null)) {
            $query->where('jenjang_id', $params['jenjang_id']);
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        // Allowed sort columns protection could be added here
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * Create a new Murid.
     */
    /**
     * Create a new Murid.
     */
    public function create(array $data): Murid
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            // 1. Create User if email provided
            $userId = null;
            if (!empty($data['email'])) {
                // Check if user exists
                $user = \App\Modules\Identity\Domain\Models\User::firstWhere('email', $data['email']);

                if (!$user) {
                    $password = !empty($data['password']) ? $data['password'] : 'password'; // Default if not set, but UI should force or generate
                    $user = \App\Modules\Identity\Domain\Models\User::create([
                        'name' => $data['nama_lengkap'],
                        'email' => $data['email'],
                        'password' => \Illuminate\Support\Facades\Hash::make($password),
                        'status' => \App\Modules\Identity\Domain\Enums\UserStatus::AKTIF,
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('Student');
                } else {
                    // Update role if needed
                    if (!$user->hasRole('Student')) {
                        $user->assignRole('Student');
                    }
                }
                $userId = $user->id;
            }

            if (empty($data['kode_murid'])) {
                // Generate Kode Murid: Registration Date (DDMMYY) + 4 Random Digits
                // Example: 0502261234
                $datePart = date('dmy');
                $randomPart = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $generatedKode = $datePart . $randomPart;

                // Ensure uniqueness
                while (\App\Modules\Student\Domain\Models\Murid::where('kode_murid', $generatedKode)->exists()) {
                    $randomPart = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                    $generatedKode = $datePart . $randomPart;
                }

                $data['kode_murid'] = $generatedKode;
            }

            // Ensure no_hp is explicitly set (null if empty)
            if (!isset($data['no_hp']) || $data['no_hp'] === '') {
                $data['no_hp'] = null;
            }

            $data['user_id'] = $userId;

            return Murid::create($data);
        });
    }

    /**
     * Show a Murid details.
     */
    public function show(Murid $murid): Murid
    {
        return $murid->load([
            'jenjang',
            'enrollments.program',
            'enrollments.paket',
            'absensi.session',
            'user' // Load linked user
        ]);
    }

    /**
     * Update a Murid.
     */
    public function update(Murid $murid, array $data): Murid
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($murid, $data) {
            // Update User if needed
            if ($murid->user_id) {
                $user = $murid->user;
                $userUpdates = [];

                if (!empty($data['nama_lengkap']) && $user->name !== $data['nama_lengkap']) {
                    $userUpdates['name'] = $data['nama_lengkap'];
                }

                if (!empty($data['email']) && $user->email !== $data['email']) {
                    // Check uniqueness if email changes
                    if (\App\Modules\Identity\Domain\Models\User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                        throw new \Exception("Email sudah digunakan oleh user lain.");
                    }
                    $userUpdates['email'] = $data['email'];
                }

                if (!empty($data['password'])) {
                    $userUpdates['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
                }

                if (!empty($userUpdates)) {
                    $user->update($userUpdates);
                }
            } else if (!empty($data['email'])) {
                // Try to link to existing or create new
                $user = \App\Modules\Identity\Domain\Models\User::firstWhere('email', $data['email']);
                if (!$user) {
                    $password = !empty($data['password']) ? $data['password'] : 'password';
                    $user = \App\Modules\Identity\Domain\Models\User::create([
                        'name' => $data['nama_lengkap'],
                        'email' => $data['email'],
                        'password' => \Illuminate\Support\Facades\Hash::make($password),
                        'status' => \App\Modules\Identity\Domain\Enums\UserStatus::AKTIF,
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('Student');
                }
                $data['user_id'] = $user->id;
            }

            $murid->update($data);
            return $murid;
        });
    }

    /**
     * Delete a Murid (Soft Delete).
     */
    public function delete(Murid $murid): void
    {
        $murid->delete();
    }

    /**
     * Bulk create multiple Murid records.
     *
     * @param array $items Array of murid data
     * @param bool $dryRun If true, validate only without creating records
     * @return array Results with summary and per-item status
     */
    public function bulkCreate(array $items, bool $dryRun = false): array
    {
        $results = [];
        $created = 0;
        $failed = 0;
        $valid = 0;

        // Pre-hash all passwords to avoid timeout (use lower cost for bulk)
        $hashedPasswords = [];
        if (!$dryRun) {
            foreach ($items as $index => $data) {
                if (!empty($data['password'])) {
                    // Use cost of 8 for bulk import (default is 10-12)
                    $hashedPasswords[$index] = \Illuminate\Support\Facades\Hash::make($data['password'], ['rounds' => 8]);
                } elseif (!empty($data['email'])) {
                    // Default password if email provided but no password
                    $hashedPasswords[$index] = \Illuminate\Support\Facades\Hash::make('password', ['rounds' => 8]);
                }
            }
        }

        foreach ($items as $index => $data) {
            $itemResult = [
                'index' => $index,
                'nama_lengkap' => $data['nama_lengkap'] ?? '',
            ];

            try {
                // Pre-check: validate email uniqueness if email is provided
                if (!empty($data['email'])) {
                    $emailExists = \App\Modules\Identity\Domain\Models\User::where('email', $data['email'])->exists();
                    if ($emailExists) {
                        // Check if existing user already linked to a murid
                        $user = \App\Modules\Identity\Domain\Models\User::where('email', $data['email'])->first();
                        $muridWithEmail = Murid::where('user_id', $user->id)->exists();
                        if ($muridWithEmail) {
                            throw new \App\Modules\Student\Exceptions\DuplicateEmailException('Email sudah digunakan.');
                        }
                    }
                }

                if ($dryRun) {
                    // Dry run: validation passed
                    $itemResult['status'] = 'valid';
                    $valid++;
                } else {
                    // Actual create using optimized bulk logic
                    $murid = $this->createBulkItem($data, $hashedPasswords[$index] ?? null);
                    $itemResult['status'] = 'created';
                    $itemResult['id'] = $murid->id;
                    $itemResult['kode_murid'] = $murid->kode_murid;
                    $created++;
                }
            } catch (\App\Modules\Student\Exceptions\DuplicateEmailException $e) {
                $failed++;
                $itemResult['status'] = 'failed';
                $itemResult['errors'] = ['email' => [$e->getMessage()]];
            } catch (\Illuminate\Database\QueryException $e) {
                $failed++;
                $itemResult['status'] = 'failed';
                // MySQL duplicate entry error code is 1062
                if ($e->errorInfo[1] === 1062) {
                    // Determine which field caused the duplicate
                    $errorMessage = $e->getMessage();
                    if (stripos($errorMessage, 'email') !== false) {
                        $itemResult['errors'] = ['email' => ['Email sudah digunakan.']];
                    } elseif (stripos($errorMessage, 'kode_murid') !== false) {
                        $itemResult['errors'] = ['kode_murid' => ['Kode murid sudah digunakan.']];
                    } else {
                        $itemResult['errors'] = ['general' => ['Data duplikat terdeteksi.']];
                    }
                } else {
                    $itemResult['errors'] = ['general' => ['Database error: ' . $e->getMessage()]];
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                $failed++;
                $itemResult['status'] = 'failed';
                $itemResult['errors'] = $e->errors();
            } catch (\Exception $e) {
                $failed++;
                $itemResult['status'] = 'failed';
                $itemResult['errors'] = ['general' => [$e->getMessage()]];
            }

            $results[] = $itemResult;
        }

        return [
            'total' => count($items),
            'created' => $created,
            'failed' => $failed,
            'valid' => $valid,
            'dry_run' => $dryRun,
            'results' => $results,
        ];
    }

    /**
     * Create a single murid item for bulk import (optimized version).
     */
    private function createBulkItem(array $data, ?string $hashedPassword): Murid
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $hashedPassword) {
            // 1. Create User if email provided
            $userId = null;
            if (!empty($data['email'])) {
                // Check if user exists
                $user = \App\Modules\Identity\Domain\Models\User::firstWhere('email', $data['email']);

                if (!$user) {
                    $user = \App\Modules\Identity\Domain\Models\User::create([
                        'name' => $data['nama_lengkap'],
                        'email' => $data['email'],
                        'password' => $hashedPassword, // Use pre-hashed password
                        'status' => \App\Modules\Identity\Domain\Enums\UserStatus::AKTIF,
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('Student');
                } else {
                    // Update role if needed
                    if (!$user->hasRole('Student')) {
                        $user->assignRole('Student');
                    }
                }
                $userId = $user->id;
            }

            if (empty($data['kode_murid'])) {
                // Generate Kode Murid: Registration Date (DDMMYY) + 4 Random Digits
                $datePart = date('dmy');
                $randomPart = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $generatedKode = $datePart . $randomPart;

                // Ensure uniqueness
                while (\App\Modules\Student\Domain\Models\Murid::where('kode_murid', $generatedKode)->exists()) {
                    $randomPart = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                    $generatedKode = $datePart . $randomPart;
                }

                $data['kode_murid'] = $generatedKode;
            }

            // Ensure no_hp is explicitly set (null if empty)
            if (!isset($data['no_hp']) || $data['no_hp'] === '') {
                $data['no_hp'] = null;
            }

            $data['user_id'] = $userId;

            return Murid::create($data);
        });
    }
}

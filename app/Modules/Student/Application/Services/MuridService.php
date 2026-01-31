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
                $dateSource = !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : date('Y-m-d');
                $timestamp = strtotime($dateSource);
                $ddmmyy = date('dmy', $timestamp);
                $random = rand(1000, 9999);

                $data['kode_murid'] = $ddmmyy . $random;
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
}

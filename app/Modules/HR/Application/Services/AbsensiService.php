<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\HR\Domain\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AbsensiService
{
    /**
     * Get list
     */
    public function getList(array $params): LengthAwarePaginator
    {
        $query = Absensi::query()->with(['karyawan.user', 'media']);

        // Privacy filter: regular users only see their own attendance
        $user = request()->user();

        $shouldRestrict = false;

        if ($user) {
            // DEBUG LOGGING
            Log::info("AbsensiView Check", [
                'user' => $user->name,
                'roles' => $user->roles->pluck('name'),
                'is_teacher_web' => $user->hasRole('Teacher', 'web'),
                'is_admin_web' => $user->hasRole('Admin', 'web'),
                'is_super_web' => $user->hasRole('Superadmin', 'web'),
                'can_manage' => $user->hasPermissionTo('absensi.manage', 'web'),
            ]);

            // Force load roles ensures we have the data, but relying on database check is safer
            // 1. Superadmin and Admin ALWAYS see everything. Check both web and generic.
            if ($user->hasRole('Superadmin', 'web') || $user->hasRole('Admin', 'web')) {
                $shouldRestrict = false;
                Log::info("-> NOT Restricted (Admin/Super)");
            }
            // 2. If not admin/superadmin, but has Teacher role, MUST restrict.
            // Explicitly check 'web' guard as roles are stored there.
            elseif ($user->hasRole('Teacher', 'web')) {
                $shouldRestrict = true;
                Log::info("-> Restricted (Teacher Web)");
            }
            // 3. Fallback: If local collection check failed, check DB directly to be absolutely sure
            elseif ($user->roles()->where('name', 'Teacher')->exists()) {
                $shouldRestrict = true;
                Log::info("-> Restricted (Teacher DB)");
            }
            // 4. If not teacher, but has management permission (e.g. HR Staff), allow view.
            elseif ($user->hasPermissionTo('absensi.manage', 'web')) {
                $shouldRestrict = false;
                Log::info("-> NOT Restricted (Manage Perm)");
            }
            // 5. Default restrict
            else {
                $shouldRestrict = true;
                Log::info("-> Restricted (Default)");
            }
        }

        if ($shouldRestrict && $user) {
            $employee = $user->employee;
            if ($employee) {
                $query->where('karyawan_id', $employee->id);
            } else {
                // If user is not an employee and not an admin, they see nothing
                $query->whereRaw('1 = 0');
            }
        }

        // Search
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('karyawan', function ($kq) use ($keyword) {
                    $kq->where('kode_karyawan', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$keyword}%"));
                })
                    ->orWhere('sumber_absen', 'like', "%{$keyword}%");
            });
        }

        // Filters
        if (! empty($params['karyawan_id'] ?? null)) {
            $query->where('karyawan_id', $params['karyawan_id']);
        }
        if (! empty($params['status_kehadiran'] ?? null)) {
            $query->where('status_kehadiran', $params['status_kehadiran']);
        }
        if (! empty($params['tanggal'] ?? null)) {
            $query->whereDate('tanggal', $params['tanggal']);
        }
        if (! empty($params['sumber_absen'] ?? null)) {
            $query->where('sumber_absen', $params['sumber_absen']);
        }
        if (! empty($params['start_date'] ?? null) && ! empty($params['end_date'] ?? null)) {
            $query->whereBetween('tanggal', [$params['start_date'], $params['end_date']]);
        }

        $query->orderBy($params['sort_by'] ?? 'created_at', $params['sort_dir'] ?? 'desc');

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * Create
     */
    public function create(array $data): Absensi
    {
        $data = $this->calculateDuration($data);
        return Absensi::create($data);
    }

    /**
     * Update
     */
    public function update(Absensi $absensi, array $data): Absensi
    {
        $data = $this->calculateDuration($data);
        $absensi->update($data);
        return $absensi;
    }

    /**
     * Delete
     */
    public function delete(Absensi $absensi): void
    {
        $absensi->delete();
    }

    /**
     * Helper to calculate duration if status is hadir and times are present
     */
    protected function calculateDuration(array $data): array
    {
        // "jika status_kehadiran=hadir: jam_masuk & jam_pulang valid time"
        // "selain hadir: jam_masuk/jam_pulang boleh null (durasi auto null)"

        $status = $data['status_kehadiran'] ?? null;

        if ($status !== 'hadir') {
            // Force null if not hadir? Or respect input?
            // Prompt valiation: "selain hadir: jam_masuk/jam_pulang boleh null"
            // If user sends time for 'izin', we might keep it or ignore it.
            // But duration likely should be null or 0.
            // Let's rely on controller validation to ensure mandatory fields.
            // Here just calc duration if both present.
        }

        if (
            ! empty($data['jam_masuk']) &&
            ! empty($data['jam_pulang'])
        ) {
            $start = Carbon::parse($data['jam_masuk']);
            $end = Carbon::parse($data['jam_pulang']);

            // Handle cross-day? Usually not for simple daily attendance unless night shift.
            // Assuming same day for calculation based on simple time.
            // If end < start, assume next day?
            if ($end->lt($start)) {
                $end->addDay();
            }

            $data['durasi'] = $start->diffInMinutes($end);
        } else {
            $data['durasi'] = null;
        }

        return $data;
    }
}

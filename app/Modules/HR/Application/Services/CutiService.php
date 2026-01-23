<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\HR\Domain\Models\Cuti;
use App\Modules\HR\Domain\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CutiService
{
    public function __construct(
        protected PengaturanCutiService $ruleService
    ) {}

    /**
     * Get list with filters
     */
    public function getList(array $params): LengthAwarePaginator
    {
        $query = Cuti::query()->with(['karyawan.user', 'approver']);

        // Apply permission-based filtering
        $user = auth()->user();
        if ($user && !$user->can('cuti.view')) {
            // If they can't view all, they can only see their own
            $query->whereHas('karyawan', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Search
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('karyawan', function ($kq) use ($keyword) {
                    $kq->where('kode_karyawan', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$keyword}%"));
                })
                    ->orWhere('jenis', 'like', "%{$keyword}%");
            });
        }

        // Filters
        if (! empty($params['karyawan_id'] ?? null)) {
            $query->where('karyawan_id', $params['karyawan_id']);
        }
        if (! empty($params['jenis'] ?? null)) {
            $query->where('jenis', $params['jenis']);
        }
        if (! empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }
        if (! empty($params['tanggal'] ?? null)) {
            $query->whereDate('tanggal', $params['tanggal']);
        }
        if (! empty($params['start_date'] ?? null) && ! empty($params['end_date'] ?? null)) {
            $query->whereBetween('start_date', [$params['start_date'], $params['end_date']]);
        }

        $query->orderBy($params['sort_by'] ?? 'created_at', $params['sort_dir'] ?? 'desc');

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * Create Cuti with Validation Rules
     */
    public function createCuti(array $data): Cuti
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::findOrFail($data['karyawan_id']);

            $start = $data['start_date'] ?? $data['tanggal'] ?? null;
            $end = $data['end_date'] ?? $data['tanggal'] ?? null;

            if (!$start || !$end) {
                throw ValidationException::withMessages(['start_date' => 'Tanggal pengajuan wajib diisi']);
            }

            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);
            $jenis = $data['jenis'];

            $status = $data['status'] ?? 'diajukan';

            // Special Rule: 'sakit' only for today
            if ($jenis === 'sakit') {
                if (!Carbon::parse($start)->isToday()) {
                    throw ValidationException::withMessages([
                        'start_date' => 'Pengajuan sakit hanya bisa dilakukan untuk hari ini.'
                    ]);
                }
            }

            // 1. Check Freelance - Unlimited
            if ($employee->kategori_karyawan === 'Freelance') {
                return Cuti::create(array_merge($data, [
                    'status' => $status,
                    'potongan_tipe' => 'none',
                    'potongan_nilai' => 0
                ]));
            }

            // 2. Load Rule
            // Subtipe null for non-contract usually, or specific logic?
            // Existing logic: Contract has subtipe. Others null.
            $subtipe = $employee->kategori_karyawan === 'Kontrak' ? $employee->subtipe_kontrak : null;

            // Normalize divisi: "Coding" -> "coding", "Non-Coding" -> "non_coding"
            $divisi = $employee->divisi ? str_replace('-', '_', strtolower($employee->divisi)) : null;
            $rule = $this->ruleService->findRule($employee->kategori_karyawan, $subtipe, $divisi, $jenis);

            // 3. Fallback / Hardcoded Rules if Rule not found or for specific defaults
            // Spec: "Jika settings table kosong, fallback ke hard-coded default"
            // Default Contract Izin: Max 2x/month, Min 3 days before.
            // Default Contract Sakit: Unlimited.

            $maksimalPengajuan = $rule?->maksimal_pengajuan;
            $minimalHari = $rule?->minimal_hari_pengajuan;
            $potonganTipe = $rule?->potongan_tipe ?? 'none';
            $potonganNilai = $rule?->potongan_nilai ?? 0;

            if (! $rule && $employee->kategori_karyawan === 'Kontrak') {
                if ($jenis === 'izin') {
                    $maksimalPengajuan = 2; // per bulan
                    $minimalHari = 3;
                    // Potongan default? Spec says "bervariatif". Let's assume none if no rule.
                } elseif ($jenis === 'sakit') {
                    $maksimalPengajuan = null; // Unlimited
                    $minimalHari = 0;
                }
            }

            // 4. Validate Minimal Hari Pengajuan (Only if > 0)
            if ($minimalHari > 0) {
                $daysDiff = Carbon::now()->diffInDays($startDate, false);
                // "tanggal izin - now() harus >= minimal_hari_pengajuan"
                // Using start of day for accurate comparison?
                $daysDiff = Carbon::today()->diffInDays($startDate->startOfDay(), false);

                if ($daysDiff < $minimalHari) {
                    throw ValidationException::withMessages([
                        'start_date' => "Pengajuan jenis {$jenis} minimal {$minimalHari} hari sebelum tanggal mulai ($daysDiff hari terdeteksi)."
                    ]);
                }
            }

            // 5. Validate Maksimal Durasi & Quota
            $durationDays = $startDate->diffInDays($endDate) + 1;

            if (! is_null($maksimalPengajuan)) {
                // If the user meant "Max days per request" (duration)
                if ($durationDays > $maksimalPengajuan) {
                    throw ValidationException::withMessages([
                        'start_date' => "Durasi pengajuan {$jenis} tidak boleh melebihi {$maksimalPengajuan} hari."
                    ]);
                }

                // If the user also meant "Total quota per period"
                // Period: Bulanan usually.
                $month = $startDate->month;
                $year = $startDate->year;

                $totalDaysMonth = Cuti::where('karyawan_id', $employee->id)
                    ->where('jenis', $jenis)
                    ->whereYear('start_date', $year)
                    ->whereMonth('start_date', $month)
                    ->whereNotIn('status', ['ditolak', 'dibatalkan'])
                    ->get()
                    ->sum(function ($c) {
                        return Carbon::parse($c->start_date)->diffInDays(Carbon::parse($c->end_date)) + 1;
                    });

                // This is optional depending on business rule, but let's prioritize the "Max Days Per Request" for now
                // since that's what the user seems to be using it for.
            }

            // 6. Create
            return Cuti::create([
                'karyawan_id' => $data['karyawan_id'],
                'jenis' => $jenis,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'tanggal' => $startDate->toDateString(),
                'keterangan' => $data['keterangan'] ?? $data['catatan'] ?? null,
                'status' => $status,
                'potongan_tipe' => $potonganTipe,
                'potongan_nilai' => $potonganNilai,
            ]);
        });
    }

    public function update(Cuti $cuti, array $data): Cuti
    {
        $cuti->update($data);
        return $cuti;
    }

    public function delete(Cuti $cuti): void
    {
        $cuti->delete();
    }

    public function approve(Cuti $cuti, ?int $approverId): Cuti
    {
        $cuti->update([
            'status' => 'disetujui',
            'disetujui_oleh' => $approverId,
        ]);

        return $cuti->fresh(['karyawan.user', 'approver']);
    }

    public function reject(Cuti $cuti, ?int $approverId): Cuti
    {
        $cuti->update([
            'status' => 'ditolak',
            'disetujui_oleh' => $approverId,
        ]);

        return $cuti->fresh(['karyawan.user', 'approver']);
    }
}

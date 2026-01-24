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
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $jenis = $data['jenis'];

            // 1. Check Freelance - Unlimited
            if ($employee->kategori_karyawan === 'Freelance') {
                return Cuti::create(array_merge($data, [
                    'status' => 'diajukan',
                    'potongan_tipe' => 'none',
                    'potongan_nilai' => 0
                ]));
            }

            // 2. Load Rule
            // Subtipe null for non-contract usually, or specific logic?
            // Existing logic: Contract has subtipe. Others null.
            $subtipe = $employee->kategori_karyawan === 'Kontrak' ? $employee->subtipe_kontrak : null;
            $rule = $this->ruleService->findRule($employee->kategori_karyawan, $subtipe, $jenis);

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

            // 5. Validate Maksimal Pengajuan (Quota)
            if (! is_null($maksimalPengajuan)) {
                // Period: Bulanan usually.
                // Check existing cuti in same month/year of start_date
                $month = $startDate->month;
                $year = $startDate->year;

                $existingDays = Cuti::where('karyawan_id', $employee->id)
                    ->where('jenis', $jenis)
                    ->whereYear('start_date', $year)
                    ->whereMonth('start_date', $month)
                    ->whereNotIn('status', ['ditolak', 'dibatalkan'])
                    ->get()
                    ->sum(fn($c) => Carbon::parse($c->start_date)->startOfDay()->diffInDays(Carbon::parse($c->end_date)->startOfDay()) + 1);

                $newDays = $startDate->startOfDay()->diffInDays($endDate->startOfDay()) + 1;

                if (($existingDays + $newDays) > $maksimal_pengajuan_value = (int)$maksimalPengajuan) {
                    $remaining = $maksimal_pengajuan_value - $existingDays;
                    $remaining = max(0, $remaining);
                    throw ValidationException::withMessages([
                        'jenis' => "Kuota {$jenis} bulan ini tidak mencukupi. Tersisa: {$remaining} hari. Pengajuan ini: {$newDays} hari."
                    ]);
                }
            }

            // 6. Create
            $bukti = $data['bukti_pendukung'] ?? null;
            $dataToSave = collect($data)->except(['bukti_pendukung'])->toArray();
            if (isset($data['catatan']) && !isset($data['keterangan'])) {
                $dataToSave['keterangan'] = $data['catatan'];
            }

            $cuti = Cuti::create(array_merge($dataToSave, [
                'status' => 'diajukan',
                'potongan_tipe' => $potonganTipe,
                'potongan_nilai' => $potonganNilai,
            ]));

            if ($bukti instanceof \Illuminate\Http\UploadedFile) {
                $cuti->addMedia($bukti)->toMediaCollection('bukti_cuti');
            }

            return $cuti;
        });
    }

    public function update(Cuti $cuti, array $data): Cuti
    {
        $bukti = $data['bukti_pendukung'] ?? null;
        $dataToUpdate = collect($data)->except(['bukti_pendukung'])->toArray();
        if (isset($data['catatan']) && !isset($data['keterangan'])) {
            $dataToUpdate['keterangan'] = $data['catatan'];
        }

        $cuti->update($dataToUpdate);

        if ($bukti instanceof \Illuminate\Http\UploadedFile) {
            $cuti->clearMediaCollection('bukti_cuti');
            $cuti->addMedia($bukti)->toMediaCollection('bukti_cuti');
        }

        return $cuti;
    }

    public function delete(Cuti $cuti): void
    {
        $cuti->delete();
    }
}

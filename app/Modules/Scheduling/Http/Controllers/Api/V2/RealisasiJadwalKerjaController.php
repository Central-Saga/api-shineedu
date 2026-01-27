<?php

namespace App\Modules\Scheduling\Http\Controllers\Api\V2;

use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use App\Modules\Scheduling\Http\Requests\StoreRealisasiRequest;
use App\Modules\Scheduling\Http\Requests\UpdateRealisasiRequest;
use App\Modules\Scheduling\Http\Resources\RealisasiJadwalKerjaResource;
use App\Shared\Http\Responses\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exports\RealisasiJadwalKerjaExport;
use Maatwebsite\Excel\Facades\Excel;

class RealisasiJadwalKerjaController
{
    public function index(Request $request): JsonResponse
    {
        $query = RealisasiJadwalKerja::query()
            ->with(['jadwalKerja.guru.user', 'approver', 'guruPengajar.user', 'guruPengganti.user']);

        // Search
        if ($keyword = $request->get('q')) {
            $query->whereHas('jadwalKerja', function ($q) use ($keyword) {
                $q->where('mata_pelajaran', 'like', "%{$keyword}%");
            })->orWhereHas('guruPengajar.user', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            });
        }

        // Filters
        if ($date = $request->get('tanggal')) {
            $query->whereDate('tanggal', $date);
        }
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($teacherId = $request->get('guru_pengajar_id')) {
            $query->where('guru_pengajar_id', $teacherId);
        }

        // Sort
        $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc');

        $perPage = (int) $request->get('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $realisasi = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            RealisasiJadwalKerjaResource::collection($realisasi),
            $realisasi,
            'Data realisasi jadwal berhasil diambil'
        );
    }

    public function sync(Request $request): JsonResponse
    {
        $dateStr = $request->get('tanggal', now()->toDateString());
        $date = Carbon::parse($dateStr);

        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        $dayName = $dayMap[$date->format('l')];

        $activeSchedules = JadwalKerja::where('hari', $dayName)
            ->where('status', 'Aktif')
            ->get();

        $createdCount = 0;
        foreach ($activeSchedules as $schedule) {
            $exists = RealisasiJadwalKerja::whereDate('tanggal', $date->toDateString())
                ->where('jadwal_kerja_id', $schedule->id)
                ->exists();

            if (!$exists) {
                RealisasiJadwalKerja::create([
                    'tanggal' => $date->toDateString(),
                    'jadwal_kerja_id' => $schedule->id,
                    'kelas_id' => $schedule->kelas_id,
                    'status' => 'diajukan',
                    'ruangan_kelas' => $schedule->ruangan_kelas,
                    'guru_pengajar_id' => $schedule->guru_pengajar_id,
                    'sumber' => 'Sistem (Auto-Sync)',
                ]);
                $createdCount++;
            }
        }

        return ApiResponse::ok([
            'created_count' => $createdCount,
            'date' => $date->toDateString(),
            'day' => $dayName,
        ], "Berhasil sinkronisasi {$createdCount} jadwal untuk hari {$dayName}");
    }

    public function store(StoreRealisasiRequest $request): JsonResponse
    {
        $realisasi = RealisasiJadwalKerja::create($request->validated());
        $realisasi->load(['jadwalKerja.guru.user', 'approver', 'guruPengajar.user', 'guruPengganti.user']);

        return ApiResponse::created(
            new RealisasiJadwalKerjaResource($realisasi),
            'Realisasi jadwal berhasil dibuat'
        );
    }

    public function show(RealisasiJadwalKerja $realisasiJadwalKerja): JsonResponse
    {
        $realisasiJadwalKerja->load(['jadwalKerja.guru.user', 'approver', 'guruPengajar.user', 'guruPengganti.user']);

        return ApiResponse::ok(
            new RealisasiJadwalKerjaResource($realisasiJadwalKerja),
            'Detail realisasi jadwal berhasil diambil'
        );
    }

    public function update(UpdateRealisasiRequest $request, RealisasiJadwalKerja $realisasiJadwalKerja): JsonResponse
    {
        $realisasiJadwalKerja->update($request->validated());
        $realisasiJadwalKerja->load(['jadwalKerja.guru.user', 'approver', 'guruPengajar.user', 'guruPengganti.user']);

        return ApiResponse::ok(
            new RealisasiJadwalKerjaResource($realisasiJadwalKerja),
            'Realisasi jadwal berhasil diperbarui'
        );
    }

    public function destroy(RealisasiJadwalKerja $realisasiJadwalKerja): JsonResponse
    {
        $realisasiJadwalKerja->delete();

        return ApiResponse::ok(null, 'Realisasi jadwal berhasil dihapus');
    }

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'realisasi_jadwal_kerja_' . date('Ymd_His');

        if ($format === 'pdf') {
            $exporter = new RealisasiJadwalKerjaExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.realisasi_jadwal_kerja', compact('items'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            'txt' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new RealisasiJadwalKerjaExport($request), $filename . '.' . $format, $ext);
    }
}

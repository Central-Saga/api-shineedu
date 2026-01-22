<?php

namespace App\Modules\Scheduling\Http\Controllers\Api\V2;

use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use App\Modules\Scheduling\Http\Requests\StoreRealisasiRequest;
use App\Modules\Scheduling\Http\Requests\UpdateRealisasiRequest;
use App\Modules\Scheduling\Http\Resources\RealisasiJadwalKerjaResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RealisasiJadwalKerjaController
{
    public function index(Request $request): JsonResponse
    {
        $query = RealisasiJadwalKerja::query()
            ->with(['jadwalKerja', 'approver', 'guruPengajar', 'guruPengganti']);

        // Search
        if ($keyword = $request->get('q')) {
            $query->whereHas('jadwalKerja', function ($q) use ($keyword) {
                $q->where('mata_pelajaran', 'like', "%{$keyword}%");
            })->orWhereHas('guruPengajar', function ($q) use ($keyword) {
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

    public function store(StoreRealisasiRequest $request): JsonResponse
    {
        $realisasi = RealisasiJadwalKerja::create($request->validated());
        $realisasi->load(['jadwalKerja', 'approver', 'guruPengajar', 'guruPengganti']);

        return ApiResponse::created(
            new RealisasiJadwalKerjaResource($realisasi),
            'Realisasi jadwal berhasil dibuat'
        );
    }

    public function show(RealisasiJadwalKerja $realisasiJadwalKerja): JsonResponse
    {
        $realisasiJadwalKerja->load(['jadwalKerja', 'approver', 'guruPengajar', 'guruPengganti']);

        return ApiResponse::ok(
            new RealisasiJadwalKerjaResource($realisasiJadwalKerja),
            'Detail realisasi jadwal berhasil diambil'
        );
    }

    public function update(UpdateRealisasiRequest $request, RealisasiJadwalKerja $realisasiJadwalKerja): JsonResponse
    {
        $realisasiJadwalKerja->update($request->validated());
        $realisasiJadwalKerja->load(['jadwalKerja', 'approver', 'guruPengajar', 'guruPengganti']);

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
}

<?php

namespace App\Modules\Scheduling\Http\Controllers\Api\V2;

use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use App\Modules\Scheduling\Http\Requests\StoreJadwalKerjaRequest;
use App\Modules\Scheduling\Http\Requests\UpdateJadwalKerjaRequest;
use App\Modules\Scheduling\Http\Resources\JadwalKerjaResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalKerjaController
{
    public function index(Request $request): JsonResponse
    {
        $query = JadwalKerja::query()->with('guru');

        // Search
        if ($keyword = $request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('mata_pelajaran', 'like', "%{$keyword}%")
                    ->orWhere('kategori', 'like', "%{$keyword}%")
                    ->orWhereHas('guru', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filters
        if ($day = $request->get('hari')) {
            $query->where('hari', $day);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($teacherId = $request->get('guru_pengajar_id')) {
            $query->where('guru_pengajar_id', $teacherId);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['hari', 'jam_mulai', 'kategori', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = (int) $request->get('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $schedules = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            JadwalKerjaResource::collection($schedules),
            $schedules,
            'Jadwal kerja berhasil diambil'
        );
    }

    public function store(StoreJadwalKerjaRequest $request): JsonResponse
    {
        $jadwal = JadwalKerja::create($request->validated());
        $jadwal->load('guru');

        return ApiResponse::created(
            new JadwalKerjaResource($jadwal),
            'Jadwal kerja berhasil dibuat'
        );
    }

    public function show(JadwalKerja $jadwalKerja): JsonResponse
    {
        $jadwalKerja->load('guru');

        return ApiResponse::ok(
            new JadwalKerjaResource($jadwalKerja),
            'Detail jadwal kerja berhasil diambil'
        );
    }

    public function update(UpdateJadwalKerjaRequest $request, JadwalKerja $jadwalKerja): JsonResponse
    {
        $jadwalKerja->update($request->validated());
        $jadwalKerja->load('guru');

        return ApiResponse::ok(
            new JadwalKerjaResource($jadwalKerja),
            'Jadwal kerja berhasil diperbarui'
        );
    }

    public function destroy(JadwalKerja $jadwalKerja): JsonResponse
    {
        $jadwalKerja->delete();

        return ApiResponse::ok(null, 'Jadwal kerja berhasil dihapus');
    }
}

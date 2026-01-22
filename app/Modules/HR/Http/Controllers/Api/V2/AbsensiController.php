<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Application\Services\AbsensiService;
use App\Modules\HR\Domain\Models\Absensi;
use App\Modules\HR\Http\Requests\StoreAbsensiRequest;
use App\Modules\HR\Http\Requests\UpdateAbsensiRequest;
use App\Modules\HR\Http\Resources\AbsensiResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsensiController
{
    public function __construct(
        protected AbsensiService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            AbsensiResource::collection($data),
            $data,
            'Data absensi berhasil diambil'
        );
    }

    public function store(StoreAbsensiRequest $request): JsonResponse
    {
        $absensi = $this->service->create($request->validated());

        return ApiResponse::created(
            new AbsensiResource($absensi->load('karyawan.user')),
            'Absensi berhasil dicatat'
        );
    }

    public function show(Absensi $absensi): JsonResponse
    {
        $absensi->load('karyawan.user');

        return ApiResponse::ok(
            new AbsensiResource($absensi),
            'Detail absensi berhasil diambil'
        );
    }

    public function update(UpdateAbsensiRequest $request, Absensi $absensi): JsonResponse
    {
        $updated = $this->service->update($absensi, $request->validated());

        return ApiResponse::ok(
            new AbsensiResource($updated->load('karyawan.user')),
            'Data absensi berhasil diperbarui'
        );
    }

    public function destroy(Absensi $absensi): JsonResponse
    {
        $this->service->delete($absensi);

        return ApiResponse::ok(null, 'Data absensi berhasil dihapus');
    }
}

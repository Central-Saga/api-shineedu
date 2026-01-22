<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Application\Services\CutiService;
use App\Modules\HR\Domain\Models\Cuti;
use App\Modules\HR\Http\Requests\StoreCutiRequest;
use App\Modules\HR\Http\Requests\UpdateCutiRequest;
use App\Modules\HR\Http\Resources\CutiResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CutiController
{
    public function __construct(
        protected CutiService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            CutiResource::collection($data),
            $data,
            'Data cuti berhasil diambil'
        );
    }

    public function store(StoreCutiRequest $request): JsonResponse
    {
        try {
            $cuti = $this->service->createCuti($request->validated());

            return ApiResponse::created(
                new CutiResource($cuti->load('karyawan.user')),
                'Pengajuan cuti berhasil dibuat'
            );
        } catch (ValidationException $e) {
            return ApiResponse::validation($e->errors(), 'Validasi Gagal');
        }
    }

    public function show(Cuti $cuti): JsonResponse
    {
        $cuti->load(['karyawan.user', 'approver']);

        return ApiResponse::ok(
            new CutiResource($cuti),
            'Detail cuti berhasil diambil'
        );
    }

    public function update(UpdateCutiRequest $request, Cuti $cuti): JsonResponse
    {
        $updated = $this->service->update($cuti, $request->validated());

        return ApiResponse::ok(
            new CutiResource($updated->load(['karyawan.user', 'approver'])),
            'Data cuti berhasil diperbarui'
        );
    }

    public function destroy(Cuti $cuti): JsonResponse
    {
        $this->service->delete($cuti);

        return ApiResponse::ok(null, 'Data cuti berhasil dihapus');
    }
}

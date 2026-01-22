<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Application\Services\PengaturanCutiService;
use App\Modules\HR\Domain\Models\PengaturanCutiRules;
use App\Modules\HR\Http\Requests\StorePengaturanCutiRequest;
use App\Modules\HR\Http\Requests\UpdatePengaturanCutiRequest;
use App\Modules\HR\Http\Resources\PengaturanCutiResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengaturanCutiController
{
    public function __construct(
        protected PengaturanCutiService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            PengaturanCutiResource::collection($data),
            $data,
            'Data pengaturan cuti berhasil diambil'
        );
    }

    public function store(StorePengaturanCutiRequest $request): JsonResponse
    {
        $rule = $this->service->store($request->validated());

        return ApiResponse::created(
            new PengaturanCutiResource($rule),
            'Pengaturan cuti berhasil ditambahkan'
        );
    }

    public function show(PengaturanCutiRules $rule): JsonResponse
    {
        return ApiResponse::ok(
            new PengaturanCutiResource($rule),
            'Detail pengaturan cuti berhasil diambil'
        );
    }

    public function update(UpdatePengaturanCutiRequest $request, PengaturanCutiRules $rule): JsonResponse
    {
        $updated = $this->service->update($rule, $request->validated());

        return ApiResponse::ok(
            new PengaturanCutiResource($updated),
            'Pengaturan cuti berhasil diperbarui'
        );
    }

    public function destroy(PengaturanCutiRules $rule): JsonResponse
    {
        $this->service->delete($rule);

        return ApiResponse::ok(null, 'Pengaturan cuti berhasil dihapus');
    }
}

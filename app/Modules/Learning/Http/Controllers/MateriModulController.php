<?php

namespace App\Modules\Learning\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Learning\Application\Services\MateriModulService;
use App\Modules\Learning\Domain\Models\MateriModul;
use App\Modules\Learning\Http\Requests\StoreMateriModulRequest;
use App\Modules\Learning\Http\Requests\UpdateMateriModulRequest;
use App\Modules\Learning\Http\Resources\MateriModulResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MateriModulController extends Controller
{
    protected $service;

    public function __construct(MateriModulService $service)
    {
        $this->service = $service;
    }

    /**
     * Get list of materi modul with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $moduls = $this->service->list($request->all());

        return ApiResponse::paginated(
            MateriModulResource::collection($moduls),
            $moduls,
            'List materi modul retrieved successfully'
        );
    }

    /**
     * Create a new materi modul.
     */
    public function store(StoreMateriModulRequest $request): JsonResponse
    {
        $modul = $this->service->create($request->validated());

        return ApiResponse::created(
            new MateriModulResource($modul),
            'Materi modul created successfully'
        );
    }

    /**
     * Get materi modul details.
     */
    public function show(int $id): JsonResponse
    {
        $modul = MateriModul::findOrFail($id);
        $modul = $this->service->show($modul);

        return ApiResponse::ok(
            new MateriModulResource($modul),
            'Materi modul details retrieved successfully'
        );
    }

    /**
     * Update materi modul.
     */
    public function update(UpdateMateriModulRequest $request, int $id): JsonResponse
    {
        $modul = MateriModul::findOrFail($id);
        $modul = $this->service->update($modul, $request->validated());

        return ApiResponse::ok(
            new MateriModulResource($modul),
            'Materi modul updated successfully'
        );
    }

    /**
     * Delete materi modul (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $modul = MateriModul::findOrFail($id);
        $this->service->delete($modul);

        return ApiResponse::ok(null, 'Materi modul deleted successfully');
    }
}

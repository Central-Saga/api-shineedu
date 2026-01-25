<?php

namespace App\Modules\Catalog\Http\Controllers\Api\V2;

use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Catalog\Http\Requests\StoreJenjangRequest;
use App\Modules\Catalog\Http\Requests\UpdateJenjangRequest;
use App\Modules\Catalog\Http\Resources\JenjangResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JenjangController
{
    public function index(Request $request): JsonResponse
    {
        $query = Jenjang::query();

        // Search
        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%");
            });
        }

        // Filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $jenjangs = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            JenjangResource::collection($jenjangs),
            $jenjangs,
            'Data jenjang berhasil diambil'
        );
    }

    public function store(StoreJenjangRequest $request): JsonResponse
    {
        $jenjang = Jenjang::create($request->validated());

        return ApiResponse::created(
            new JenjangResource($jenjang),
            'Jenjang berhasil ditambahkan'
        );
    }

    public function show(Jenjang $jenjang): JsonResponse
    {
        return ApiResponse::ok(
            new JenjangResource($jenjang),
            'Detail jenjang berhasil diambil'
        );
    }

    public function update(UpdateJenjangRequest $request, Jenjang $jenjang): JsonResponse
    {
        $jenjang->update($request->validated());

        return ApiResponse::ok(
            new JenjangResource($jenjang),
            'Data jenjang berhasil diperbarui'
        );
    }

    public function destroy(Jenjang $jenjang): JsonResponse
    {
        $jenjang->delete();

        return ApiResponse::ok(null, 'Jenjang berhasil dihapus');
    }
}

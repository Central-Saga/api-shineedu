<?php

namespace App\Modules\Catalog\Http\Controllers\Api\V2;

use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Catalog\Http\Requests\StorePaketRequest;
use App\Modules\Catalog\Http\Requests\UpdatePaketRequest;
use App\Modules\Catalog\Http\Resources\PaketResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaketController
{
    public function index(Request $request): JsonResponse
    {
        $query = Paket::query();

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
        if ($tipe = $request->input('tipe')) {
            $query->where('tipe', $tipe);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pakets = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            PaketResource::collection($pakets),
            $pakets,
            'Data paket berhasil diambil'
        );
    }

    public function store(StorePaketRequest $request): JsonResponse
    {
        $paket = Paket::create($request->validated());

        return ApiResponse::created(
            new PaketResource($paket),
            'Paket berhasil ditambahkan'
        );
    }

    public function show(Paket $paket): JsonResponse
    {
        return ApiResponse::ok(
            new PaketResource($paket),
            'Detail paket berhasil diambil'
        );
    }

    public function update(UpdatePaketRequest $request, Paket $paket): JsonResponse
    {
        $paket->update($request->validated());

        return ApiResponse::ok(
            new PaketResource($paket),
            'Data paket berhasil diperbarui'
        );
    }

    public function destroy(Paket $paket): JsonResponse
    {
        $paket->delete();

        return ApiResponse::ok(null, 'Paket berhasil dihapus');
    }
}

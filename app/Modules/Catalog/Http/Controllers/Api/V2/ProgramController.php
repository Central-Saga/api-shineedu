<?php

namespace App\Modules\Catalog\Http\Controllers\Api\V2;

use App\Modules\Catalog\Domain\Models\Program;
use App\Modules\Catalog\Http\Requests\StoreProgramRequest;
use App\Modules\Catalog\Http\Requests\UpdateProgramRequest;
use App\Modules\Catalog\Http\Resources\ProgramResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController
{
    public function index(Request $request): JsonResponse
    {
        $query = Program::query()->with('jenjangs');

        // Search
        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%")
                    ->orWhere('deskripsi', 'like', "%{$q}%");
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

        $programs = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            ProgramResource::collection($programs),
            $programs,
            'Data program berhasil diambil'
        );
    }

    public function store(StoreProgramRequest $request): JsonResponse
    {
        $data = $request->validated();
        $program = Program::create($data);

        if (isset($data['jenjang_ids'])) {
            $program->jenjangs()->sync($data['jenjang_ids']);
        }

        return ApiResponse::created(
            new ProgramResource($program->load('jenjangs')),
            'Program berhasil ditambahkan'
        );
    }

    public function show(Program $program): JsonResponse
    {
        return ApiResponse::ok(
            new ProgramResource($program->load('jenjangs')),
            'Detail program berhasil diambil'
        );
    }

    public function update(UpdateProgramRequest $request, Program $program): JsonResponse
    {
        $data = $request->validated();
        $program->update($data);

        if (isset($data['jenjang_ids'])) {
            $program->jenjangs()->sync($data['jenjang_ids']);
        }

        return ApiResponse::ok(
            new ProgramResource($program->load('jenjangs')),
            'Data program berhasil diperbarui'
        );
    }

    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return ApiResponse::ok(null, 'Program berhasil dihapus');
    }
}

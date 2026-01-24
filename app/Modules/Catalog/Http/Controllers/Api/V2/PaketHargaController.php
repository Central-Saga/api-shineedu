<?php

namespace App\Modules\Catalog\Http\Controllers\Api\V2;

use App\Modules\Catalog\Application\Services\PricingService;
use App\Modules\Catalog\Domain\Models\PaketHarga;
use App\Modules\Catalog\Http\Requests\StorePaketHargaRequest;
use App\Modules\Catalog\Http\Requests\UpdatePaketHargaRequest;
use App\Modules\Catalog\Http\Resources\PaketHargaResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaketHargaController
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PaketHarga::query()->with(['program', 'jenjang', 'paket']);

        // Search not typically applied to pricing table generally effectively, maybe by related names?
        // Let's stick strictly to filters for structured data

        // Filters
        if ($pid = $request->input('program_id')) {
            $query->where('program_id', $pid);
        }
        if ($jid = $request->input('jenjang_id')) {
            $query->where('jenjang_id', $jid);
        }
        if ($pkid = $request->input('paket_id')) {
            $query->where('paket_id', $pkid);
        }
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

        $prices = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            PaketHargaResource::collection($prices),
            $prices,
            'Data harga paket berhasil diambil'
        );
    }

    public function store(StorePaketHargaRequest $request): JsonResponse
    {
        // Validation including overlap check is handled in Request
        $price = PaketHarga::create($request->validated());

        return ApiResponse::created(
            new PaketHargaResource($price->load(['program', 'jenjang', 'paket'])),
            'Harga paket berhasil ditambahkan'
        );
    }

    public function show(PaketHarga $harga): JsonResponse
    {
        return ApiResponse::ok(
            new PaketHargaResource($harga->load(['program', 'jenjang', 'paket'])),
            'Detail harga paket berhasil diambil'
        );
    }

    public function update(UpdatePaketHargaRequest $request, PaketHarga $harga): JsonResponse
    {
        $harga->update($request->validated());

        return ApiResponse::ok(
            new PaketHargaResource($harga->load(['program', 'jenjang', 'paket'])),
            'Data harga paket berhasil diperbarui'
        );
    }

    public function destroy(PaketHarga $harga): JsonResponse
    {
        $harga->delete();

        return ApiResponse::ok(null, 'Harga paket berhasil dihapus');
    }

    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'program_id' => 'required|integer',
            'jenjang_id' => 'required|integer',
            'paket_id' => 'required|integer',
            'jumlah_siswa' => 'required|integer|min:1',
            'tanggal' => 'nullable|date',
        ]);

        $price = $this->pricingService->lookupPrice(
            $request->query('program_id'),
            $request->query('jenjang_id'),
            $request->query('paket_id'),
            $request->query('jumlah_siswa'),
            $request->query('tanggal')
        );

        if (! $price) {
            return ApiResponse::fail('Tidak menjumpai harga yang cocok untuk konfigurasi ini.', 404);
        }

        return ApiResponse::ok(
            new PaketHargaResource($price->load(['program', 'jenjang', 'paket'])),
            'Harga ditemukan'
        );
    }
}

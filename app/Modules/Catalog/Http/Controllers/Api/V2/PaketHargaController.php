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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaketHargaExport;

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

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'paket_harga_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new PaketHargaExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.paket_harga', compact('items'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new PaketHargaExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new PaketHargaExport($request);
            $handle = fopen('php://output', 'w');
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");
            $exporter->query()->chunk(100, function ($items) use ($handle, $exporter) {
                foreach ($items as $item) {
                    fwrite($handle, implode("\t", $exporter->map($item)) . "\n");
                }
            });
            fclose($handle);
        }, $filename . '.txt', ['Content-Type' => 'text/plain']);
    }

    protected function exportSql(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new PaketHargaExport($request);
            $query = $exporter->query();
            $handle = fopen('php://output', 'w');
            fwrite($handle, "-- Paket Harga Data Export\n\n");
            $query->chunk(100, function ($items) use ($handle) {
                foreach ($items as $item) {
                    $sql = sprintf(
                        "INSERT INTO paket_harga (id, program_id, jenjang_id, paket_id, min_siswa, max_siswa, harga, status, created_at, updated_at) VALUES (%d, %d, %d, %d, %d, %d, %s, '%s', '%s', '%s') ON DUPLICATE KEY UPDATE program_id=VALUES(program_id), jenjang_id=VALUES(jenjang_id), paket_id=VALUES(paket_id), min_siswa=VALUES(min_siswa), max_siswa=VALUES(max_siswa), harga=VALUES(harga), status=VALUES(status), updated_at=VALUES(updated_at);\n",
                        $item->id,
                        $item->program_id,
                        $item->jenjang_id,
                        $item->paket_id,
                        $item->min_siswa,
                        $item->max_siswa,
                        $item->harga,
                        $item->status,
                        $item->created_at,
                        $item->updated_at
                    );
                    fwrite($handle, $sql);
                }
            });
            fclose($handle);
        }, $filename . '.sql', ['Content-Type' => 'application/sql']);
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

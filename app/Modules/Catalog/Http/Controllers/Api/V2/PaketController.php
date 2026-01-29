<?php

namespace App\Modules\Catalog\Http\Controllers\Api\V2;

use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Catalog\Http\Requests\StorePaketRequest;
use App\Modules\Catalog\Http\Requests\UpdatePaketRequest;
use App\Modules\Catalog\Http\Resources\PaketResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaketExport;

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

        if ($request->filled('program_id') || $request->filled('jenjang_id')) {
            $query->whereHas('hargas', function ($q) use ($request) {
                if ($request->filled('program_id')) {
                    $q->where('program_id', $request->input('program_id'));
                }
                if ($request->filled('jenjang_id')) {
                    $q->where('jenjang_id', $request->input('jenjang_id'));
                }
                $q->where('status', 'Aktif');
            });
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

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'paket_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new PaketExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.paket', compact('items'))
                ->setPaper('a4', 'portrait');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new PaketExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new PaketExport($request);
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
            $exporter = new PaketExport($request);
            $query = $exporter->query();
            $handle = fopen('php://output', 'w');
            fwrite($handle, "-- Paket Data Export\n\n");
            $query->chunk(100, function ($items) use ($handle) {
                foreach ($items as $item) {
                    $sql = sprintf(
                        "INSERT INTO paket (id, kode, nama, tipe, pertemuan_per_bulan, durasi_menit, status, created_at, updated_at) VALUES (%d, '%s', '%s', '%s', %d, %d, '%s', %s, %s) ON DUPLICATE KEY UPDATE kode=VALUES(kode), nama=VALUES(nama), tipe=VALUES(tipe), pertemuan_per_bulan=VALUES(pertemuan_per_bulan), durasi_menit=VALUES(durasi_menit), status=VALUES(status), updated_at=VALUES(updated_at);\n",
                        $item->id,
                        addslashes($item->kode),
                        addslashes($item->nama),
                        $item->tipe,
                        $item->pertemuan_per_bulan,
                        $item->durasi_menit,
                        $item->status,
                        $item->created_at ? "'" . $item->created_at->format('Y-m-d H:i:s') . "'" : "NULL",
                        $item->updated_at ? "'" . $item->updated_at->format('Y-m-d H:i:s') . "'" : "NULL"
                    );
                    fwrite($handle, $sql);
                }
            });
            fclose($handle);
        }, $filename . '.sql', ['Content-Type' => 'application/sql']);
    }
}

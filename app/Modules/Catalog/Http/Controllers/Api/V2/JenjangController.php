<?php

namespace App\Modules\Catalog\Http\Controllers\Api\V2;

use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Catalog\Http\Requests\StoreJenjangRequest;
use App\Modules\Catalog\Http\Requests\UpdateJenjangRequest;
use App\Modules\Catalog\Http\Resources\JenjangResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JenjangExport;

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

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'jenjang_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new JenjangExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.jenjang', compact('items'))
                ->setPaper('a4', 'portrait');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new JenjangExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new JenjangExport($request);
            $handle = fopen('php://output', 'w');
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");
            $exporter->query()->chunk(100, function ($items) use ($handle, $exporter) {
                /** @var Jenjang $item */
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
            $exporter = new JenjangExport($request);
            $query = $exporter->query();
            $handle = fopen('php://output', 'w');
            fwrite($handle, "-- Jenjang Data Export\n\n");
            $query->chunk(100, function ($items) use ($handle) {
                /** @var Jenjang $item */
                foreach ($items as $item) {
                    $sql = sprintf(
                        "INSERT INTO jenjang (id, kode, nama, status, created_at, updated_at) VALUES (%d, '%s', '%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE kode=VALUES(kode), nama=VALUES(nama), status=VALUES(status), updated_at=VALUES(updated_at);\n",
                        $item->id,
                        addslashes($item->kode),
                        addslashes($item->nama),
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
}

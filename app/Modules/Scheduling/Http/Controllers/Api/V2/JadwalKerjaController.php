<?php

namespace App\Modules\Scheduling\Http\Controllers\Api\V2;

use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use App\Modules\Scheduling\Application\Services\JadwalKerjaBulkService;
use App\Modules\Scheduling\Http\Requests\StoreJadwalKerjaRequest;
use App\Modules\Scheduling\Http\Requests\UpdateJadwalKerjaRequest;
use App\Modules\Scheduling\Http\Resources\JadwalKerjaResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exports\JadwalKerjaExport;
use Maatwebsite\Excel\Facades\Excel;

class JadwalKerjaController
{
    protected JadwalKerjaBulkService $bulkService;

    public function __construct(JadwalKerjaBulkService $bulkService)
    {
        $this->bulkService = $bulkService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = JadwalKerja::query()->with('guru.user');

        if ($request->has('kelas_id')) {
            $kelasId = $request->get('kelas_id');
            if ($kelasId === 'null') {
                $query->whereNull('kelas_id');
            } else {
                $query->where('kelas_id', $kelasId);
            }
        }

        // Search
        if ($keyword = $request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('mata_pelajaran', 'like', "%{$keyword}%")
                    ->orWhere('kategori', 'like', "%{$keyword}%")
                    ->orWhereHas('guru.user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filters
        if ($day = $request->get('hari')) {
            $query->where('hari', $day);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($teacherId = $request->get('guru_pengajar_id')) {
            $query->where('guru_pengajar_id', $teacherId);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['hari', 'jam_mulai', 'kategori', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = (int) $request->get('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $schedules = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            JadwalKerjaResource::collection($schedules),
            $schedules,
            'Jadwal kerja berhasil diambil'
        );
    }

    public function store(StoreJadwalKerjaRequest $request): JsonResponse
    {
        $jadwal = JadwalKerja::create($request->validated());
        $jadwal->load('guru.user');

        return ApiResponse::created(
            new JadwalKerjaResource($jadwal),
            'Jadwal kerja berhasil dibuat'
        );
    }

    public function show(JadwalKerja $jadwalKerja): JsonResponse
    {
        $jadwalKerja->load('guru.user');

        return ApiResponse::ok(
            new JadwalKerjaResource($jadwalKerja),
            'Detail jadwal kerja berhasil diambil'
        );
    }

    public function update(UpdateJadwalKerjaRequest $request, JadwalKerja $jadwalKerja): JsonResponse
    {
        $jadwalKerja->update($request->validated());
        $jadwalKerja->load('guru.user');

        return ApiResponse::ok(
            new JadwalKerjaResource($jadwalKerja),
            'Jadwal kerja berhasil diperbarui'
        );
    }

    public function destroy(JadwalKerja $jadwalKerja): JsonResponse
    {
        $jadwalKerja->delete();

        return ApiResponse::ok(null, 'Jadwal kerja berhasil dihapus');
    }

    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'dry_run' => 'boolean',
        ]);

        $items = $request->input('items', []);
        $dryRun = $request->boolean('dry_run', false);

        $result = $this->bulkService->bulkCreate($items, $dryRun);

        return ApiResponse::ok($result, 'Bulk operation completed');
    }

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'jadwal_kerja_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new JadwalKerjaExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.jadwal_kerja', compact('items'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new JadwalKerjaExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    /**
     * Helper to export TXT.
     */
    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new JadwalKerjaExport($request);
            $handle = fopen('php://output', 'w');

            // Headings
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");

            $exporter->query()->chunk(100, function ($items) use ($handle, $exporter) {
                foreach ($items as $item) {
                    fwrite($handle, implode("\t", $exporter->map($item)) . "\n");
                }
            });

            fclose($handle);
        }, $filename . '.txt', [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Helper to export SQL.
     */
    protected function exportSql(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new JadwalKerjaExport($request);
            $query = $exporter->query();

            $handle = fopen('php://output', 'w');

            fwrite($handle, "-- Shine Education Bali - Jadwal Kerja Data Export\n");
            fwrite($handle, "-- Generated at " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $query->chunk(100, function ($jadwals) use ($handle) {
                foreach ($jadwals as $jadwal) {
                    $vals = [
                        $jadwal->id,
                        $jadwal->kelas_id ?? 'NULL',
                        addslashes((string)$jadwal->hari),
                        addslashes((string)$jadwal->jam_mulai),
                        addslashes((string)$jadwal->jam_selesai),
                        addslashes((string)$jadwal->mata_pelajaran),
                        $jadwal->guru_pengajar_id ?? 'NULL',
                        addslashes((string)$jadwal->kategori),
                        addslashes((string)$jadwal->status),
                        addslashes((string)$jadwal->ruangan_kelas),
                        addslashes((string)$jadwal->created_at),
                        addslashes((string)$jadwal->updated_at),
                    ];
                    $sql = sprintf(
                        "INSERT INTO jadwal_kerja (id, kelas_id, hari, jam_mulai, jam_selesai, mata_pelajaran, guru_pengajar_id, kategori, status, ruangan_kelas, created_at, updated_at) VALUES (%d, %s, '%s', '%s', '%s', '%s', %s, '%s', '%s', '%s', '%s', '%s');\n",
                        ...$vals
                    );
                    fwrite($handle, $sql);
                }
            });

            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
        }, $filename . '.sql', [
            'Content-Type' => 'application/sql',
        ]);
    }
}

<?php

namespace App\Modules\Academic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\KelasService;
use App\Modules\Academic\Http\Requests\StoreKelasRequest;
use App\Modules\Academic\Http\Requests\UpdateKelasRequest;
use App\Modules\Academic\Http\Resources\KelasResource;
use App\Modules\Academic\Domain\Models\Kelas;
use App\Shared\Http\Responses\ApiResponse;
use App\Exports\KelasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    private $kelasService;

    public function __construct(KelasService $kelasService)
    {
        $this->kelasService = $kelasService;
    }

    public function index(Request $request)
    {
        $data = $this->kelasService->listKelas($request->all());
        return ApiResponse::paginated(KelasResource::collection($data), $data, 'List Kelas');
    }

    public function store(StoreKelasRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $kelas = $this->kelasService->storeKelas($data);

        return ApiResponse::created(new KelasResource($kelas), 'Kelas berhasil dibuat');
    }

    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas = $this->kelasService->showKelas($kelas);

        return ApiResponse::ok(new KelasResource($kelas), 'Detail kelas');
    }

    public function update(UpdateKelasRequest $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas = $this->kelasService->updateKelas($kelas, $request->validated());

        return ApiResponse::ok(new KelasResource($kelas), 'Kelas berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $this->kelasService->deleteKelas($kelas);

        return ApiResponse::ok(null, 'Kelas berhasil dihapus');
    }

    /**
     * Export kelas.
     */
    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'kelas_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new KelasExport($request);
            $kelas = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.kelas', compact('kelas'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new KelasExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    /**
     * Helper to export TXT.
     */
    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new KelasExport($request);
            $handle = fopen('php://output', 'w');

            // Headings
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");

            $exporter->query()->chunk(100, function ($kelasEntries) use ($handle, $exporter) {
                foreach ($kelasEntries as $kelas) {
                    fwrite($handle, implode("\t", $exporter->map($kelas)) . "\n");
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
            $exporter = new KelasExport($request);
            $query = $exporter->query();

            $handle = fopen('php://output', 'w');

            fwrite($handle, "-- Shine Education Bali - Kelas Data Export\n");
            fwrite($handle, "-- Generated at " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $query->chunk(100, function ($kelasEntries) use ($handle) {
                foreach ($kelasEntries as $kelas) {
                    $vals = [
                        $kelas->id,
                        addslashes((string)$kelas->kode_kelas),
                        addslashes((string)$kelas->nama_kelas),
                        $kelas->program_id ?? 'NULL',
                        $kelas->jenjang_id ?? 'NULL',
                        addslashes((string)$kelas->tipe_kelas),
                        $kelas->mode_private ? 1 : 0,
                        $kelas->kapasitas ?? 'NULL',
                        addslashes((string)$kelas->status),
                        $kelas->periode_mulai ? "'" . $kelas->periode_mulai->format('Y-m-d') . "'" : 'NULL',
                        $kelas->periode_selesai ? "'" . $kelas->periode_selesai->format('Y-m-d') . "'" : 'NULL',
                        addslashes((string)$kelas->ruangan_default),
                        $kelas->created_by ?? 'NULL',
                        $kelas->created_at ? "'" . $kelas->created_at->format('Y-m-d H:i:s') . "'" : "NULL",
                        $kelas->updated_at ? "'" . $kelas->updated_at->format('Y-m-d H:i:s') . "'" : "NULL",
                    ];
                    $sql = sprintf(
                        "INSERT INTO kelas (id, kode_kelas, nama_kelas, program_id, jenjang_id, tipe_kelas, mode_private, kapasitas, status, periode_mulai, periode_selesai, ruangan_default, created_by, created_at, updated_at) VALUES (%d, '%s', '%s', %s, %s, '%s', %d, %s, '%s', %s, %s, '%s', %s, %s, %s) ON DUPLICATE KEY UPDATE nama_kelas=VALUES(nama_kelas), status=VALUES(status);\n",
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

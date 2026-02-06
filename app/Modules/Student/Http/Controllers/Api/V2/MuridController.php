<?php

namespace App\Modules\Student\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\Student\Application\Services\MuridService;
use App\Modules\Student\Domain\Models\Murid;
use App\Modules\Student\Http\Requests\StoreMuridRequest;
use App\Modules\Student\Http\Requests\UpdateMuridRequest;
use App\Modules\Student\Http\Resources\MuridResource;
use App\Shared\Http\Responses\ApiResponse;
use App\Exports\MuridExport;
use App\Imports\MuridImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MuridController extends Controller
{
    protected $muridService;

    public function __construct(MuridService $muridService)
    {
        $this->muridService = $muridService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->muridService->getList($request->all());

        return MuridResource::collection($data)->additional([
            'success' => true,
            'message' => 'Data murid berhasil diambil',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMuridRequest $request)
    {
        $murid = $this->muridService->create($request->validated());

        return (new MuridResource($murid))->additional([
            'success' => true,
            'message' => 'Murid berhasil ditambahkan',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Murid $murid)
    {
        $murid = $this->muridService->show($murid);

        return (new MuridResource($murid))->additional([
            'success' => true,
            'message' => 'Detail murid berhasil diambil',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMuridRequest $request, Murid $murid)
    {
        $murid = $this->muridService->update($murid, $request->validated());

        return (new MuridResource($murid))->additional([
            'success' => true,
            'message' => 'Data murid berhasil diperbarui',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Murid $murid)
    {
        $this->muridService->delete($murid);

        return ApiResponse::ok(null, 'Murid berhasil dihapus');
    }

    /**
     * Export murid.
     */
    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'murid_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new MuridExport($request);
            $murids = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.murid', compact('murids'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new MuridExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    /**
     * Helper to export TXT.
     */
    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new MuridExport($request);
            $handle = fopen('php://output', 'w');

            // Headings
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");

            $exporter->query()->chunk(100, function ($murids) use ($handle, $exporter) {
                /** @var Murid $murid */
                foreach ($murids as $murid) {
                    fwrite($handle, implode("\t", $exporter->map($murid)) . "\n");
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
            $exporter = new MuridExport($request);
            $query = $exporter->query();

            $handle = fopen('php://output', 'w');

            fwrite($handle, "-- Shine Education Bali - Murid Data Export\n");
            fwrite($handle, "-- Generated at " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $query->chunk(100, function ($murids) use ($handle) {
                /** @var Murid $murid */
                foreach ($murids as $murid) {
                    $vals = [
                        $murid->id,
                        addslashes((string)$murid->kode_murid),
                        addslashes((string)$murid->nama_lengkap),
                        addslashes((string)$murid->jenis_kelamin),
                        $murid->tanggal_lahir ? "'" . $murid->tanggal_lahir->format('Y-m-d') . "'" : 'NULL',
                        addslashes((string)$murid->no_hp),
                        addslashes((string)$murid->email),
                        addslashes((string)$murid->alamat),
                        $murid->jenjang_id ?? 'NULL',
                        addslashes((string)$murid->sekolah_asal),
                        addslashes((string)$murid->kelas_sekolah),
                        addslashes((string)$murid->nama_wali),
                        addslashes((string)$murid->no_hp_wali),
                        addslashes((string)$murid->email_wali),
                        addslashes((string)$murid->hubungan_wali),
                        $murid->status,
                        $murid->created_at ? "'" . $murid->created_at->format('Y-m-d H:i:s') . "'" : "NULL",
                        $murid->updated_at ? "'" . $murid->updated_at->format('Y-m-d H:i:s') . "'" : "NULL",
                    ];
                    $sql = sprintf(
                        "INSERT INTO murid (id, kode_murid, nama_lengkap, jenis_kelamin, tanggal_lahir, no_hp, email, alamat, jenjang_id, sekolah_asal, kelas_sekolah, nama_wali, no_hp_wali, email_wali, hubungan_wali, status, created_at, updated_at) VALUES (%d, '%s', '%s', '%s', %s, '%s', '%s', '%s', %s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s, %s) ON DUPLICATE KEY UPDATE nama_lengkap=VALUES(nama_lengkap), status=VALUES(status);\n",
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
    /**
     * Import murid from Excel/CSV/txt/sql OR handle bulk JSON.
     */
    public function import(Request $request)
    {
        // Handle JSON Bulk payload (from frontend dry-run/import)
        if ($request->isJson() && $request->has('items')) {
            $data = $request->validate([
                'items' => 'required|array',
                'items.*.nama_lengkap' => 'required|string',
                'dry_run' => 'boolean',
            ]);

            $items = $request->input('items', []);
            $dryRun = $request->boolean('dry_run', false);

            $result = $this->muridService->bulkCreate($items, $dryRun);

            return response()->json($result);
        }

        // Handle File Upload
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt,sql',
        ]);

        $file = $request->file('file');

        Excel::import(new MuridImport, $file);

        return ApiResponse::ok(null, 'Data murid berhasil diimport');
    }
}

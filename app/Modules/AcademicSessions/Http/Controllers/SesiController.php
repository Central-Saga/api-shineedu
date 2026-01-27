<?php

namespace App\Modules\AcademicSessions\Http\Controllers;

use App\Modules\AcademicSessions\Application\Services\GenerateSesiService;
use App\Modules\AcademicSessions\Application\Services\SesiService;
use App\Modules\AcademicSessions\Http\Resources\SessionResource;
use App\Shared\Http\Responses\ApiResponse; // Verify path or use standard
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SessionExport;

class SesiController
{
    public function __construct(
        protected SesiService $sesiService,
        protected GenerateSesiService $generateService
    ) {}

    public function indexByKelas(Request $request, $kelasId): JsonResponse
    {
        // TODO: Validate user can view this kelas session (Permission)

        $filters = [
            'start_date' => $request->get('from'),
            'end_date' => $request->get('to'),
            'status_sesi' => $request->get('status_sesi'),
            'per_page' => $request->get('per_page'),
        ];

        $sessions = $this->sesiService->getSesiByKelas($kelasId, $filters);

        return ApiResponse::paginated(
            SessionResource::collection($sessions),
            $sessions,
            'Data sesi berhasil diambil'
        );
    }

    public function show($id): JsonResponse
    {
        $session = $this->sesiService->findById($id);

        return ApiResponse::ok(
            new SessionResource($session),
            'Detail sesi berhasil diambil'
        );
    }

    public function generate(Request $request, $kelasId): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'sumber' => 'in:SYSTEM,MANUAL',
            'auto_populate_absensi' => 'boolean'
        ]);

        $result = $this->generateService->generateForKelas(
            $kelasId,
            $request->get('from'),
            $request->get('to'),
            $request->get('sumber', 'SYSTEM'),
            $request->boolean('auto_populate_absensi', true)
        );

        return ApiResponse::ok(
            ['count' => $result['count']], // Optional data return
            $result['message']
        );
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Validation handled inside Service or Request. Do basic generic validation here or generic Request
        $validData = $request->validate([
            'status_sesi' => 'string|in:TERJADWAL,BERJALAN,SELESAI,BATAL,LIBUR',
            'status_kehadiran_guru' => 'string|in:HADIR,IZIN,SAKIT,ALPHA,DIGANTI',
            'jam_mulai_aktual' => 'nullable|date_format:H:i', // Or datetime depending on frontend, Service expects string H:i or handle it
            'jam_selesai_aktual' => 'nullable|date_format:H:i',
            'catatan' => 'nullable|string',
            'guru_pengganti_id' => 'nullable|exists:users,id', // or employees
            'ruangan_kelas' => 'nullable|string',
            'alasan_batal' => 'nullable|string',
            'is_hangus' => 'boolean'
        ]);

        $session = $this->sesiService->update($id, $validData, auth()->id());

        return ApiResponse::ok(
            new SessionResource($session),
            'Sesi berhasil diperbarui'
        );
    }

    public function syncAnggota($id): JsonResponse
    {
        $count = $this->sesiService->syncAnggota($id);

        return ApiResponse::ok(
            ['added_count' => $count],
            'Sinkronisasi anggota berhasil'
        );
    }

    public function export(Request $request, $kelasId = null)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'sesi_' . ($kelasId ? 'kelas_' . $kelasId . '_' : '') . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename, $kelasId);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename, $kelasId);
        }

        if ($format === 'pdf') {
            $exporter = new SessionExport($request, $kelasId);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.session', compact('items'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new SessionExport($request, $kelasId), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    protected function exportTxt(Request $request, string $filename, $kelasId = null)
    {
        return response()->streamDownload(function () use ($request, $kelasId) {
            $exporter = new SessionExport($request, $kelasId);
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

    protected function exportSql(Request $request, string $filename, $kelasId = null)
    {
        return response()->streamDownload(function () use ($request, $kelasId) {
            $exporter = new SessionExport($request, $kelasId);
            $query = $exporter->query();
            $handle = fopen('php://output', 'w');
            fwrite($handle, "-- Session Data Export\n\n");
            $query->chunk(100, function ($items) use ($handle) {
                foreach ($items as $item) {
                    $sql = sprintf(
                        "INSERT INTO realisasi_jadwal_kerja (id, kelas_id, tanggal, jam_mulai_aktual, jam_selesai_aktual, guru_pengajar_id, status_sesi, created_at, updated_at) VALUES (%d, %d, '%s', '%s', '%s', %d, '%s', '%s', '%s') ON DUPLICATE KEY UPDATE status_sesi=VALUES(status_sesi), updated_at=VALUES(updated_at);\n",
                        $item->id,
                        $item->kelas_id,
                        $item->tanggal->format('Y-m-d'),
                        $item->jam_mulai_aktual,
                        $item->jam_selesai_aktual,
                        $item->guru_pengajar_id,
                        $item->status_sesi,
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

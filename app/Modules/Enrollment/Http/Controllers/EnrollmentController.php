<?php

namespace App\Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Application\Services\EnrollmentService;
use App\Modules\Enrollment\Http\Requests\StoreEnrollmentRequest;
use App\Modules\Enrollment\Http\Requests\UpdateEnrollmentRequest;
use App\Modules\Enrollment\Http\Resources\EnrollmentResource;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EnrollmentExport;
use App\Shared\Http\Responses\ApiResponse;

class EnrollmentController extends Controller
{
    protected $service;

    public function __construct(EnrollmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        // Permission check handled by route middleware usually, but good to have backup or authorize resource
        // $this->authorize('viewAny', Enrollment::class);

        $enrollments = $this->service->list($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List Enrollments retrieved successfully',
            'data' => EnrollmentResource::collection($enrollments),
            'meta' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ],
        ]);
    }

    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $enrollment = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Enrollment created successfully',
            'data' => new EnrollmentResource($enrollment),
        ], 201);
    }

    /**
     * Import enrollment dari external system (i-seller/legacy)
     * untuk tracking absensi, logbook, dan sisa pertemuan saja
     * 
     * Mode saldo:
     * - saldo_override diisi dengan angka (>=0) = pakai saldo manual
     * - saldo_override = -1 = unlimited (tidak dicek)
     * - saldo_override tidak diisi + jumlah_pertemuan diisi = otomatis set saldo manual
     * - saldo_override tidak diisi + jumlah_pertemuan tidak diisi = pakai sistem ledger
     */
    public function importExternal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Murid: bisa pakai murid_id yang sudah ada atau buat murid baru
            'murid_id' => ['nullable', 'exists:murid,id'],
            'murid_baru' => ['nullable', 'array', 'required_without:murid_id'],
            'murid_baru.nama_lengkap' => ['required_with:murid_baru', 'string', 'max:255'],
            'murid_baru.no_hp' => ['required_with:murid_baru', 'string', 'max:20'],
            'murid_baru.jenis_kelamin' => ['nullable', 'in:L,P'],
            'murid_baru.tanggal_lahir' => ['nullable', 'date'],
            'murid_baru.alamat' => ['nullable', 'string'],
            'murid_baru.nama_wali' => ['nullable', 'string', 'max:255'],
            'murid_baru.no_hp_wali' => ['nullable', 'string', 'max:20'],

            // Enrollment data
            'program_id' => ['required', 'exists:program,id'],
            'jenjang_id' => ['required', 'exists:jenjang,id'],
            'paket_id' => ['required', 'exists:paket,id'],
            'jumlah_siswa' => ['nullable', 'integer', 'min:1'],
            'harga_final' => ['nullable', 'numeric', 'min:0'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan' => ['nullable', 'string'],
            'biaya_pendaftaran_amount' => ['nullable', 'numeric', 'min:0'],

            // External system tracking
            'sumber' => ['nullable', 'string', 'in:ISELLER,IMPORT'],
            'external_reference_id' => ['nullable', 'string'],
            
            // Saldo options
            'saldo_override' => ['nullable', 'integer'], // >=0 = manual, -1 = unlimited
            'jumlah_pertemuan' => ['nullable', 'integer', 'min:0'], // Kalau diisi tanpa saldo_override, akan jadi saldo manual
            'tanggal_pembayaran' => ['nullable', 'date'],
            'tanggal_berakhir' => ['nullable', 'date'],
            
            // Optional: langsung assign ke kelas
            'kelas_id' => ['nullable', 'exists:kelas,id'],
        ]);

        $enrollment = $this->service->createFromExternal($validated);

        // Tentukan mode saldo untuk response
        $saldoMode = 'ledger';
        if ($enrollment->saldo_override === -1) {
            $saldoMode = 'unlimited';
        } elseif ($enrollment->saldo_override !== null) {
            $saldoMode = 'manual';
        }

        return response()->json([
            'success' => true,
            'message' => 'Enrollment imported from external system successfully',
            'data' => new EnrollmentResource($enrollment),
            'meta' => [
                'saldo_mode' => $saldoMode,
                'saldo_current' => $enrollment->saldo_override,
                'is_from_external' => $enrollment->isFromExternal(),
            ],
        ], 201);
    }

    /**
     * Update saldo override untuk enrollment
     * Admin/guru bisa update saldo manual kapan saja
     * 
     * @param Request $request
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function updateSaldoOverride(Request $request, Enrollment $enrollment): JsonResponse
    {
        $validated = $request->validate([
            'saldo_override' => ['required', 'integer', 'min:-1'],
        ]);

        $oldValue = $enrollment->saldo_override;
        $enrollment = $this->service->updateSaldoOverride($enrollment, $validated['saldo_override']);

        // Determine mode description
        $mode = 'manual';
        if ($validated['saldo_override'] === -1) {
            $mode = 'unlimited';
        } elseif ($validated['saldo_override'] === null) {
            $mode = 'ledger';
        }

        return response()->json([
            'success' => true,
            'message' => "Saldo updated successfully to {$mode} mode",
            'data' => [
                'enrollment' => new EnrollmentResource($enrollment),
                'saldo_previous' => $oldValue,
                'saldo_current' => $enrollment->saldo_override,
                'mode' => $mode,
            ],
        ]);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        $enrollment = $this->service->show($enrollment);

        // Tambah info saldo mode
        $saldoMode = 'ledger';
        if ($enrollment->saldo_override === -1) {
            $saldoMode = 'unlimited';
        } elseif ($enrollment->saldo_override !== null) {
            $saldoMode = 'manual';
        }

        return response()->json([
            'success' => true,
            'message' => 'Enrollment details retrieved successfully',
            'data' => new EnrollmentResource($enrollment),
            'meta' => [
                'saldo_mode' => $saldoMode,
                'saldo_override' => $enrollment->saldo_override,
                'is_from_external' => $enrollment->isFromExternal(),
            ],
        ]);
    }

    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        $enrollment = $this->service->update($enrollment, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Enrollment updated successfully',
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    public function destroy(Enrollment $enrollment): JsonResponse
    {
        $this->service->delete($enrollment);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment deleted successfully',
        ]);
    }

    public function updateRegistrationFeeStatus(Request $request, Enrollment $enrollment): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:UNPAID,PAID,WAIVED'],
            'due_date' => ['nullable', 'date'],
        ]);

        $enrollment = $this->service->updateRegistrationFeeStatus($enrollment, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Registration fee status updated successfully',
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'enrollment_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new EnrollmentExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.enrollment', compact('items'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new EnrollmentExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new EnrollmentExport($request);
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
            $exporter = new EnrollmentExport($request);
            $query = $exporter->query();
            $handle = fopen('php://output', 'w');
            fwrite($handle, "-- Enrollment Data Export\n\n");
            $query->chunk(100, function ($items) use ($handle) {
                foreach ($items as $item) {
                    $sql = sprintf(
                        "INSERT INTO enrollments (id, murid_id, program_id, jenjang_id, paket_id, status, created_at, updated_at) VALUES (%d, %d, %d, %d, %d, '%s', %s, %s) ON DUPLICATE KEY UPDATE status=VALUES(status), updated_at=VALUES(updated_at);\n",
                        $item->id,
                        $item->murid_id,
                        $item->program_id,
                        $item->jenjang_id,
                        $item->paket_id,
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

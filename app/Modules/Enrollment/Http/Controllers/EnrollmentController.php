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

    public function show(Enrollment $enrollment): JsonResponse
    {
        $enrollment = $this->service->show($enrollment);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment details retrieved successfully',
            'data' => new EnrollmentResource($enrollment),
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
                        "INSERT INTO enrollments (id, murid_id, program_id, jenjang_id, paket_id, kelas_id, status, created_at, updated_at) VALUES (%d, %d, %d, %d, %d, %s, '%s', '%s', '%s') ON DUPLICATE KEY UPDATE status=VALUES(status), updated_at=VALUES(updated_at);\n",
                        $item->id,
                        $item->murid_id,
                        $item->program_id,
                        $item->jenjang_id,
                        $item->paket_id,
                        $item->kelas_id ?? 'NULL',
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

<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Http\Requests\EmployeeIndexRequest;
use App\Modules\HR\Http\Requests\StoreEmployeeRequest;
use App\Modules\HR\Http\Requests\UpdateEmployeeRequest;
use App\Modules\HR\Http\Resources\EmployeeResource;
use App\Modules\HR\Repositories\EmployeeRepositoryInterface;
use App\Modules\HR\Services\EmployeeService;

use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;


class EmployeeController
{
    public function __construct(
        protected EmployeeRepositoryInterface $repository,
        protected EmployeeService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(EmployeeIndexRequest $request): JsonResponse
    {
        $employees = $this->repository->paginate($request->validated());

        return ApiResponse::paginated(
            EmployeeResource::collection($employees),
            $employees,
            'Data karyawan berhasil diambil'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->service->create($request->validated());

        return ApiResponse::created(
            new EmployeeResource($employee->load('user')),
            'Karyawan berhasil ditambahkan'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): JsonResponse
    {
        $employee->load('user');

        return ApiResponse::ok(
            new EmployeeResource($employee),
            'Detail karyawan berhasil diambil'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $this->service->update($employee, $request->validated());

        return ApiResponse::ok(
            new EmployeeResource($employee->fresh()->load('user')),
            'Data karyawan berhasil diperbarui'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return ApiResponse::ok(null, 'Karyawan berhasil dihapus');
    }

    /**
     * Export employees.
     */
    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'employees_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new EmployeesExport($request);
            $employees = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.employees', compact('employees'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            'txt' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new EmployeesExport($request), $filename . '.' . $format, $ext);
    }

    /**
     * Helper to export SQL.
     */
    protected function exportSql(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new EmployeesExport($request);
            $query = $exporter->query();

            $handle = fopen('php://output', 'w');

            $query->chunk(100, function ($employees) use ($handle) {
                foreach ($employees as $employee) {
                    $vals = [
                        addslashes($employee->kode_karyawan),
                        $employee->user_id ?? 'NULL',
                        addslashes($employee->kategori_karyawan),
                        addslashes($employee->created_at),
                        addslashes($employee->updated_at),
                    ];
                    // Simplify for example, should include all columns
                    $sql = sprintf(
                        "INSERT INTO karyawan (kode_karyawan, user_id, kategori_karyawan, created_at, updated_at) VALUES ('%s', %s, '%s', '%s', '%s');\n",
                        ...$vals
                    );
                    fwrite($handle, $sql);
                }
            });

            fclose($handle);
        }, $filename . '.sql', [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Import employees.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt,sql,xls',
        ]);

        try {
            Excel::import(new EmployeesImport, $request->file('file'));

            return ApiResponse::ok(null, 'Import karyawan berhasil');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return ApiResponse::validation($errors, 'Validation Error');
        } catch (\Exception $e) {
            return ApiResponse::fail($e->getMessage(), 500);
        }
    }
}

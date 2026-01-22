<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Domain\Models\Employee;
use App\Modules\HR\Http\Requests\EmployeeIndexRequest;
use App\Modules\HR\Http\Requests\StoreEmployeeRequest;
use App\Modules\HR\Http\Requests\UpdateEmployeeRequest;
use App\Modules\HR\Http\Resources\EmployeeResource;


use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Modules\HR\Application\Services\UnifiedEmployeeImportService;


class EmployeeController
{
    public function __construct(
        protected UnifiedEmployeeImportService $importService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(EmployeeIndexRequest $request): JsonResponse
    {
        $params = $request->validated();
        $query = Employee::query()->with('user');

        // Search: kode_karyawan OR user name/email
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('kode_karyawan', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filters
        if (! empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }
        if (! empty($params['kategori_karyawan'] ?? null)) {
            $query->where('kategori_karyawan', $params['kategori_karyawan']);
        }
        if (! empty($params['subtipe_kontrak'] ?? null)) {
            $query->where('subtipe_kontrak', $params['subtipe_kontrak']);
        }
        if (! empty($params['tipe_gaji'] ?? null)) {
            $query->where('tipe_gaji', $params['tipe_gaji']);
        }

        // Sort
        $sortWhitelist = [
            'kode_karyawan',
            'status',
            'kategori_karyawan',
            'tipe_gaji',
            'gaji_pokok',
            'created_at',
            'updated_at',
        ];

        $sortBy = in_array($params['sort_by'] ?? null, $sortWhitelist, true)
            ? $params['sort_by']
            : 'created_at';
        $sortDir = in_array(strtolower((string) ($params['sort_dir'] ?? '')), ['asc', 'desc'], true)
            ? strtolower((string) $params['sort_dir'])
            : 'desc';
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) ($params['per_page'] ?? 15);
        $perPage = min(max($perPage, 1), 100);

        $employees = $query->paginate($perPage)->withQueryString();

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
        $employee = Employee::create($request->validated());

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
        $employee->update($request->validated());

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
            $query = $exporter->query(); // This already includes with(['user.roles'])

            $handle = fopen('php://output', 'w');

            // Header info
            fwrite($handle, "-- Shine Education Bali - Employee & User Data Export\n");
            fwrite($handle, "-- Generated at " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $query->chunk(100, function ($employees) use ($handle) {
                foreach ($employees as $employee) {
                    $user = $employee->user;

                    // 1. Export User if exists
                    if ($user) {
                        $userStatus = $user->status instanceof \BackedEnum ? $user->status->value : $user->status;
                        $userVals = [
                            $user->id,
                            addslashes($user->name),
                            addslashes($user->email),
                            addslashes($user->password),
                            addslashes((string)$userStatus),
                            addslashes((string)$user->created_at),
                            addslashes((string)$user->updated_at),
                        ];
                        $userSql = sprintf(
                            "INSERT INTO users (id, name, email, password, status, created_at, updated_at) VALUES (%d, '%s', '%s', '%s', '%s', '%s', '%s') ON DUPLICATE KEY UPDATE name=VALUES(name), status=VALUES(status);\n",
                            ...$userVals
                        );
                        fwrite($handle, $userSql);

                        // 2. Export Roles (model_has_roles)
                        foreach ($user->roles as $role) {
                            $roleSql = sprintf(
                                "INSERT INTO model_has_roles (role_id, model_type, model_id) SELECT id, 'App\\\\Modules\\\\Identity\\\\Domain\\\\Models\\\\User', %d FROM roles WHERE name='%s' ON DUPLICATE KEY UPDATE role_id=role_id;\n",
                                $user->id,
                                addslashes($role->name)
                            );
                            fwrite($handle, $roleSql);
                        }
                    }

                    // 3. Export Karyawan
                    $vals = [
                        addslashes($employee->kode_karyawan),
                        $employee->user_id ?? 'NULL',
                        addslashes($employee->kategori_karyawan),
                        $employee->subtipe_kontrak ? "'" . addslashes($employee->subtipe_kontrak) . "'" : 'NULL',
                        addslashes($employee->tipe_gaji),
                        $employee->gaji_pokok ?? 0,
                        $employee->bank_nama ? "'" . addslashes($employee->bank_nama) . "'" : 'NULL',
                        $employee->bank_no_rekening ? "'" . addslashes($employee->bank_no_rekening) . "'" : 'NULL',
                        $employee->nomor_hp ? "'" . addslashes($employee->nomor_hp) . "'" : 'NULL',
                        $employee->alamat ? "'" . addslashes($employee->alamat) . "'" : 'NULL',
                        $employee->tanggal_lahir ? "'" . $employee->tanggal_lahir->format('Y-m-d') . "'" : 'NULL',
                        addslashes($employee->status),
                        addslashes((string)$employee->created_at),
                        addslashes((string)$employee->updated_at),
                    ];
                    $sql = sprintf(
                        "INSERT INTO karyawan (kode_karyawan, user_id, kategori_karyawan, subtipe_kontrak, tipe_gaji, gaji_pokok, bank_nama, bank_no_rekening, nomor_hp, alamat, tanggal_lahir, status, created_at, updated_at) VALUES ('%s', %s, '%s', %s, '%s', %s, %s, %s, %s, %s, %s, '%s', '%s', '%s') ON DUPLICATE KEY UPDATE status=VALUES(status), kategori_karyawan=VALUES(kategori_karyawan);\n",
                        ...$vals
                    );
                    fwrite($handle, $sql);
                    fwrite($handle, "\n");
                }
            });

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
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

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'sql') {
            try {
                $sql = file_get_contents($file->getRealPath());
                if (empty($sql)) {
                    return ApiResponse::fail('File SQL kosong', 422);
                }
                \Illuminate\Support\Facades\DB::unprepared($sql);
                return ApiResponse::ok(null, 'Import karyawan dari SQL berhasil');
            } catch (\Exception $e) {
                return ApiResponse::fail('Gagal melakukan import SQL: ' . $e->getMessage(), 500);
            }
        }

        try {
            // Optional: configure dynamically if needed
            // $this->importService->setConfig(['user_mode' => 'update']);

            $report = $this->importService->import($file);

            if (!empty($report['errors']) && $report['employees_inserted'] === 0 && $report['employees_updated'] === 0) {
                // If everything failed validation or DB, return as validation error
                $errorMessages = array_map(fn($e) => is_array($e) ? $e['message'] : $e, $report['errors']);
                return ApiResponse::validation($errorMessages, 'Import failed/Validation Error');
            }

            return ApiResponse::ok($report, 'Proses import selesai');
        } catch (\Exception $e) {
            return ApiResponse::fail($e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

use App\Modules\Identity\Http\Requests\RoleIndexRequest;
use App\Modules\Identity\Http\Requests\RoleStoreRequest;
use App\Modules\Identity\Http\Requests\RoleSyncPermissionsRequest;
use App\Modules\Identity\Http\Requests\RoleUpdateRequest;
use App\Modules\Identity\Http\Resources\RoleResource;
use App\Modules\Identity\Models\User;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

use Spatie\Permission\Models\Role;
use App\Exports\RolesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;


class RoleController
{
    /**
     * Display a listing of the resource.
     */
    public function index(RoleIndexRequest $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $allowedSort = ['name', 'created_at', 'updated_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSort) ? $request->get('sort_by') : 'name';
        $sortDir = in_array(strtolower((string) $request->get('sort_dir')), ['asc', 'desc']) ? strtolower((string) $request->get('sort_dir')) : 'asc';

        $query = Role::query()->with('permissions');

        if ($keyword = $request->get('q')) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $query->orderBy($sortBy, $sortDir);
        $roles = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            RoleResource::collection($roles),
            $roles,
            'Data role berhasil diambil'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleStoreRequest $request): JsonResponse
    {
        $role = Role::create([
            'name' => $request->validated()['name'],
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions') && !empty($request->validated()['permissions'])) {
            $permissions = Permission::whereIn('name', $request->validated()['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');

        return ApiResponse::created(
            new RoleResource($role),
            'Role berhasil dibuat'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return ApiResponse::ok(
            new RoleResource($role),
            'Data role berhasil diambil'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleUpdateRequest $request, Role $role): JsonResponse
    {
        // Protect superadmin role
        if ($role->name === 'superadmin') {
            return ApiResponse::forbidden('Role superadmin tidak dapat diubah.');
        }

        $validated = $request->validated();

        if (isset($validated['name'])) {
            $role->name = $validated['name'];
            $role->save();
        }

        if ($request->has('permissions') && !empty($validated['permissions'])) {
            $permissions = Permission::whereIn('name', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');

        return ApiResponse::ok(
            new RoleResource($role),
            'Role berhasil diupdate'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Protect superadmin role
        if ($role->name === 'superadmin') {
            return ApiResponse::forbidden('Role superadmin tidak dapat dihapus.');
        }

        // Check if role is assigned to any user
        if (User::role($role->name)->exists()) {
            return ApiResponse::conflict('Role masih digunakan oleh user dan tidak dapat dihapus.');
        }

        $role->delete();

        return ApiResponse::ok(null, 'Role berhasil dihapus');
    }

    /**
     * Sync permissions to role.
     */
    public function syncPermissions(RoleSyncPermissionsRequest $request, Role $role): JsonResponse
    {
        $permissions = Permission::whereIn('name', $request->validated()['permissions'])->get();
        $role->syncPermissions($permissions);

        $role->load('permissions');

        return ApiResponse::ok(
            new RoleResource($role),
            'Permissions berhasil disinkronkan'
        );
    }

    /**
     * Export roles.
     */
    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'roles_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new \App\Exports\RolesExport($request);
            $roles = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.roles', compact('roles'))
                ->setPaper('a4', 'portrait');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            'txt' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new \App\Exports\RolesExport($request), $filename . '.' . $format, $ext);
    }

    /**
     * Helper to export SQL.
     */
    protected function exportSql(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new RolesExport($request);
            $query = $exporter->query();

            $handle = fopen('php://output', 'w');

            $query->chunk(100, function ($roles) use ($handle) {
                foreach ($roles as $role) {
                    $vals = [
                        addslashes($role->name),
                        addslashes($role->guard_name),
                        addslashes($role->created_at),
                        addslashes($role->updated_at),
                    ];
                    $sql = sprintf(
                        "INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES ('%s', '%s', '%s', '%s');\n",
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
     * Import roles.
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
                return ApiResponse::ok(null, 'Import roles dari SQL berhasil');
            } catch (\Exception $e) {
                return ApiResponse::fail('Gagal melakukan import SQL: ' . $e->getMessage(), 500);
            }
        }

        // Spreadsheet import for roles could be added here if needed.
        return ApiResponse::fail('Import spreadsheet untuk role belum didukung.', 400);
    }
}

<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

use App\Modules\Identity\Http\Requests\UserIndexRequest;
use App\Modules\Identity\Http\Requests\UserStoreRequest;
use App\Modules\Identity\Http\Requests\UserUpdateRequest;
use App\Modules\Identity\Http\Requests\UserUpdateRoleRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use App\Modules\Identity\Domain\Models\User;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;


class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $allowedSort = ['name', 'email', 'status', 'created_at', 'updated_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSort) ? $request->get('sort_by') : 'created_at';
        $sortDir = in_array(strtolower((string) $request->get('sort_dir')), ['asc', 'desc']) ? strtolower((string) $request->get('sort_dir')) : 'desc';

        $query = User::query()->with('roles.permissions');

        if ($keyword = $request->get('q')) {
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($role = $request->get('role')) {
            $query->whereHas('roles', fn($r) => $r->where('name', $role));
        }

        $query->orderBy($sortBy, $sortDir);
        $users = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            UserResource::collection($users),
            $users,
            'Data user berhasil diambil'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);

        // Assign single role
        $role = Role::findByName($validated['role'], 'web');
        $user->syncRoles([$role]);

        $user->load('roles.permissions');

        return ApiResponse::created(
            new UserResource($user),
            'User berhasil dibuat'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $user->load('roles.permissions');

        return ApiResponse::ok(
            new UserResource($user),
            'Data user berhasil diambil'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['status'])) {
            $user->status = $validated['status'];
        }

        // Update password if provided and not empty
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->load('roles.permissions');

        return ApiResponse::ok(
            new UserResource($user),
            'User berhasil diupdate'
        );
    }

    /**
     * Update user role.
     */
    public function updateRole(UserUpdateRoleRequest $request, User $user): JsonResponse
    {
        $role = Role::findByName($request->validated()['role'], 'web');

        // Sync single role (replaces existing roles)
        $user->syncRoles([$role]);

        $user->load('roles.permissions');

        return ApiResponse::ok(
            new UserResource($user),
            'Role user berhasil diupdate'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete user
        $user->delete();

        return ApiResponse::ok(null, 'User berhasil dihapus');
    }

    /**
     * Export users.
     */
    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'users_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new UsersExport($request);
            $users = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.users', compact('users'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new UsersExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    /**
     * Helper to export TXT.
     */
    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new UsersExport($request);
            $handle = fopen('php://output', 'w');

            // Headings
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");

            $exporter->query()->chunk(100, function ($users) use ($handle, $exporter) {
                foreach ($users as $user) {
                    fwrite($handle, implode("\t", $exporter->map($user)) . "\n");
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
            $exporter = new UsersExport($request);
            $query = $exporter->query();

            $handle = fopen('php://output', 'w');

            $query->chunk(100, function ($users) use ($handle) {
                foreach ($users as $user) {
                    $status = $user->status;
                    if ($status instanceof \BackedEnum) {
                        $status = $status->value;
                    } elseif ($status instanceof \UnitEnum) {
                        $status = $status->name;
                    }

                    $vals = [
                        addslashes($user->name),
                        addslashes($user->email),
                        addslashes($user->password),
                        addslashes($status),
                        addslashes($user->created_at),
                        addslashes($user->updated_at),
                    ];
                    $sql = sprintf(
                        "INSERT INTO users (name, email, password, status, created_at, updated_at) VALUES ('%s', '%s', '%s', '%s', '%s', '%s');\n",
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
     * Import users.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt,sql,xls',
            'update' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $updateExisting = $request->boolean('update', false);

        if ($extension === 'sql') {
            try {
                $sql = file_get_contents($file->getRealPath());
                if (empty($sql)) {
                    return ApiResponse::fail('File SQL kosong', 422);
                }
                \Illuminate\Support\Facades\DB::unprepared($sql);
                return ApiResponse::ok(null, 'Import users dari SQL berhasil');
            } catch (\Exception $e) {
                return ApiResponse::fail('Gagal melakukan import SQL: ' . $e->getMessage(), 500);
            }
        }

        try {
            $importer = new UsersImport($updateExisting);
            Excel::import($importer, $file);

            $report = $importer->getReport();

            return ApiResponse::ok($report, 'Import users berhasil');
        } catch (\Exception $e) {
            return ApiResponse::fail($e->getMessage(), 500);
        }
    }
}

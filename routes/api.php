<?php

use App\Modules\HR\Http\Controllers\Api\V2\EmployeeController;
use App\Modules\Identity\Http\Controllers\Api\V2\AuthController;
use App\Modules\Identity\Http\Controllers\Api\V2\PermissionController;
use App\Modules\Identity\Http\Controllers\Api\V2\RoleController;
use App\Modules\Identity\Http\Controllers\Api\V2\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API V2 Routes
Route::prefix('v2')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Permissions
        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
        Route::get('/roles/export', [RoleController::class, 'export'])->middleware('permission:roles.view'); // Falls back to view permission
        Route::post('/roles/import', [RoleController::class, 'import'])->middleware('permission:roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update');
        Route::patch('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update');
        Route::put('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:roles.manage');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');

        // Users
        Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
        Route::get('/users/export', [UserController::class, 'export'])->middleware('permission:users.view'); // Falls back to view
        Route::post('/users/import', [UserController::class, 'import'])->middleware('permission:users.create'); // Requires create permission
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create');
        Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
        Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->middleware('permission:users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete');

        // Employees
        Route::get('/employees', [EmployeeController::class, 'index'])->middleware('permission:employees.view');
        Route::get('/employees/export', [EmployeeController::class, 'export'])->middleware('permission:employees.view');
        Route::post('/employees/import', [EmployeeController::class, 'import'])->middleware('permission:employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employees.create');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees.view');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update');
        Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete');

        // Scheduling (Jadwal Kerja & Realisasi)
        Route::get('/jadwal-kerja', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'index'])->middleware('permission:scheduling.view');
        Route::post('/jadwal-kerja', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'store'])->middleware('permission:scheduling.create');
        Route::get('/jadwal-kerja/{jadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'show'])->middleware('permission:scheduling.view');
        Route::put('/jadwal-kerja/{jadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'update'])->middleware('permission:scheduling.update');
        Route::delete('/jadwal-kerja/{jadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'destroy'])->middleware('permission:scheduling.delete');

        Route::get('/realisasi-jadwal', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'index'])->middleware('permission:scheduling.view');
        Route::post('/realisasi-jadwal', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'store'])->middleware('permission:scheduling.create');
        Route::get('/realisasi-jadwal/{realisasiJadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'show'])->middleware('permission:scheduling.view');
        Route::put('/realisasi-jadwal/{realisasiJadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'update'])->middleware('permission:scheduling.update');
        Route::delete('/realisasi-jadwal/{realisasiJadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'destroy'])->middleware('permission:scheduling.delete');
    });
});

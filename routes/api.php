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
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/employees/export', [EmployeeController::class, 'export'])->middleware('permission:employees.view');
        Route::post('/employees/import', [EmployeeController::class, 'import'])->middleware('permission:employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employees.create');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees.view');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update');
        Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete');

        // Scheduling (Jadwal Kerja & Realisasi)
        Route::get('/jadwal-kerja', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'index'])->middleware('permission:jadwal_kerja.view');
        Route::get('/jadwal-kerja/export', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'export'])->middleware('permission:jadwal_kerja.view');
        Route::post('/jadwal-kerja', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'store'])->middleware('permission:jadwal_kerja.create');
        Route::get('/jadwal-kerja/{jadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'show'])->middleware('permission:jadwal_kerja.view');
        Route::put('/jadwal-kerja/{jadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'update'])->middleware('permission:jadwal_kerja.update');
        Route::delete('/jadwal-kerja/{jadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\JadwalKerjaController::class, 'destroy'])->middleware('permission:jadwal_kerja.delete');

        Route::get('/realisasi-jadwal-kerja', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'index'])->middleware('permission:realisasi_jadwal_kerja.view');
        Route::get('/realisasi-jadwal-kerja/export', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'export'])->middleware('permission:realisasi_jadwal_kerja.view');
        Route::post('/realisasi-jadwal-kerja/sync', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'sync'])->middleware('permission:realisasi_jadwal_kerja.create');
        Route::post('/realisasi-jadwal-kerja', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'store'])->middleware('permission:realisasi_jadwal_kerja.create');
        Route::get('/realisasi-jadwal-kerja/{realisasiJadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'show'])->middleware('permission:realisasi_jadwal_kerja.view');
        Route::put('/realisasi-jadwal-kerja/{realisasiJadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'update'])->middleware('permission:realisasi_jadwal_kerja.update');
        Route::delete('/realisasi-jadwal-kerja/{realisasiJadwalKerja}', [\App\Modules\Scheduling\Http\Controllers\Api\V2\RealisasiJadwalKerjaController::class, 'destroy'])->middleware('permission:realisasi_jadwal_kerja.delete');

        // Pengaturan Cuti
        Route::get('/pengaturan-cuti', [\App\Modules\HR\Http\Controllers\Api\V2\PengaturanCutiController::class, 'index']);
        Route::post('/pengaturan-cuti', [\App\Modules\HR\Http\Controllers\Api\V2\PengaturanCutiController::class, 'store'])->middleware('permission:pengaturan_cuti.create');
        Route::get('/pengaturan-cuti/{rule}', [\App\Modules\HR\Http\Controllers\Api\V2\PengaturanCutiController::class, 'show'])->middleware('permission:pengaturan_cuti.view');
        Route::put('/pengaturan-cuti/{rule}', [\App\Modules\HR\Http\Controllers\Api\V2\PengaturanCutiController::class, 'update'])->middleware('permission:pengaturan_cuti.update');
        Route::patch('/pengaturan-cuti/{rule}', [\App\Modules\HR\Http\Controllers\Api\V2\PengaturanCutiController::class, 'update'])->middleware('permission:pengaturan_cuti.update');
        Route::delete('/pengaturan-cuti/{rule}', [\App\Modules\HR\Http\Controllers\Api\V2\PengaturanCutiController::class, 'destroy'])->middleware('permission:pengaturan_cuti.delete');

        // Cuti
        Route::get('/cuti', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'index']);
        Route::get('/cuti/export', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'export'])->middleware('permission:cuti.view');
        Route::post('/cuti', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'store'])->middleware('permission:cuti.create');
        Route::get('/cuti/{cuti}', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'show'])->middleware('permission:cuti.view');
        Route::put('/cuti/{cuti}', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'update'])->middleware('permission:cuti.update');
        Route::patch('/cuti/{cuti}', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'update'])->middleware('permission:cuti.update');
        Route::delete('/cuti/{cuti}', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'destroy'])->middleware('permission:cuti.delete');
        Route::post('/cuti/{cuti}/approve', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'approve'])->middleware('permission:cuti.manage');
        Route::post('/cuti/{cuti}/reject', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'reject'])->middleware('permission:cuti.manage');

        // Absensi
        Route::get('/absensi/today', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'todayStatus'])->middleware('permission:absensi.create');
        Route::get('/absensi/export', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'export'])->middleware('permission:absensi.view');
        Route::post('/absensi/check-in', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'checkIn'])->middleware('permission:absensi.create');
        Route::post('/absensi/check-out', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'checkOut'])->middleware('permission:absensi.create');
        Route::get('/absensi', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'index'])->middleware('permission:absensi.view');
        Route::post('/absensi', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'store'])->middleware('permission:absensi.manage');
        Route::get('/absensi/{absensi}', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'show'])->middleware('permission:absensi.view');
        Route::put('/absensi/{absensi}', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'update'])->middleware('permission:absensi.update');
        Route::patch('/absensi/{absensi}', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'update'])->middleware('permission:absensi.update');
        Route::delete('/absensi/{absensi}', [\App\Modules\HR\Http\Controllers\Api\V2\AbsensiController::class, 'destroy'])->middleware('permission:absensi.delete');

        // Rekap Bulanan
        Route::get('/rekap-bulanan', [\App\Modules\HR\Http\Controllers\Api\V2\RekapBulananController::class, 'index'])->middleware('permission:rekap_bulanan.view');
        Route::get('/rekap-bulanan/{id}', [\App\Modules\HR\Http\Controllers\Api\V2\RekapBulananController::class, 'show'])->middleware('permission:rekap_bulanan.view');

        // Penggajian
        Route::get('/payrolls', [\App\Modules\HR\Http\Controllers\Api\V2\PayrollController::class, 'index'])->middleware('permission:gaji.view');
        Route::post('/payrolls/generate', [\App\Modules\HR\Http\Controllers\Api\V2\PayrollController::class, 'generate'])->middleware('permission:gaji.manage');
        Route::get('/payrolls/{id}/export', [\App\Modules\HR\Http\Controllers\Api\V2\PayrollController::class, 'export'])->middleware('permission:gaji.view');
        Route::get('/payrolls/{id}', [\App\Modules\HR\Http\Controllers\Api\V2\PayrollController::class, 'show'])->middleware('permission:gaji.view');
        Route::put('/payrolls/{id}/status', [\App\Modules\HR\Http\Controllers\Api\V2\PayrollController::class, 'updateStatus'])->middleware('permission:gaji.manage');
    });
});

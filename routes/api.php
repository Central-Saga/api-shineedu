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
    // Public routes (no auth)
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Public catalog (read-only for landing form)
    Route::prefix('public')->group(function () {
        Route::get('catalog/jenjang', [\App\Modules\Catalog\Http\Controllers\Api\V2\JenjangController::class, 'index']);
        Route::get('catalog/program', [\App\Modules\Catalog\Http\Controllers\Api\V2\ProgramController::class, 'index']);
        Route::get('catalog/paket', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketController::class, 'index']);
        Route::get('catalog/harga', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'index']);
        Route::get('catalog/harga/lookup', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'lookup']);
        Route::post('landing-register', [\App\Modules\Enrollment\Http\Controllers\LandingRegisterController::class, 'store']);
        Route::get('gallery', [\App\Modules\Landing\Http\Controllers\Api\V2\LandingGalleryPublicController::class, 'index']);
        Route::get('job-vacancies', [\App\Modules\HR\Http\Controllers\Api\V2\JobVacancyController::class, 'index']);
        Route::post('job-applications', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'store']);
        Route::post('job-applications/track', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'track']);
    });

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
        Route::post('/cuti/{cuti}/cancel', [\App\Modules\HR\Http\Controllers\Api\V2\CutiController::class, 'cancel']);

        // Job Applications (Lamaran Kerja)
        Route::get('/job-applications', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'index'])->middleware('permission:job_application.view');
        Route::post('/job-applications', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'store'])->middleware('permission:job_application.create');
        Route::get('/job-applications/{job_application}', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'show'])->middleware('permission:job_application.view');
        Route::put('/job-applications/{job_application}', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'update'])->middleware('permission:job_application.update');
        Route::patch('/job-applications/{job_application}', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'update'])->middleware('permission:job_application.update');
        Route::delete('/job-applications/{job_application}', [\App\Modules\HR\Http\Controllers\Api\V2\JobApplicationController::class, 'destroy'])->middleware('permission:job_application.delete');

        // Job Vacancies (Lowongan Kerja)
        Route::get('/job-vacancies', [\App\Modules\HR\Http\Controllers\Api\V2\JobVacancyController::class, 'list'])->middleware('permission:job_vacancy.view');
        Route::post('/job-vacancies', [\App\Modules\HR\Http\Controllers\Api\V2\JobVacancyController::class, 'store'])->middleware('permission:job_vacancy.create');
        Route::get('/job-vacancies/{job_vacancy}', [\App\Modules\HR\Http\Controllers\Api\V2\JobVacancyController::class, 'show'])->middleware('permission:job_vacancy.view');
        Route::put('/job-vacancies/{job_vacancy}', [\App\Modules\HR\Http\Controllers\Api\V2\JobVacancyController::class, 'update'])->middleware('permission:job_vacancy.update');
        Route::patch('/job-vacancies/{job_vacancy}', [\App\Modules\HR\Http\Controllers\Api\V2\JobVacancyController::class, 'update'])->middleware('permission:job_vacancy.update');
        Route::delete('/job-vacancies/{job_vacancy}', [\App\Modules\HR\Http\Controllers\Api\V2\JobVacancyController::class, 'destroy'])->middleware('permission:job_vacancy.delete');

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


        // Murid
        Route::get('/murid', [\App\Modules\Student\Http\Controllers\Api\V2\MuridController::class, 'index'])->middleware('permission:student.view');
        Route::get('/murid/export', [\App\Modules\Student\Http\Controllers\Api\V2\MuridController::class, 'export'])->middleware('permission:student.view');
        Route::post('/murid', [\App\Modules\Student\Http\Controllers\Api\V2\MuridController::class, 'store'])->middleware('permission:student.create');
        Route::get('/murid/{murid}', [\App\Modules\Student\Http\Controllers\Api\V2\MuridController::class, 'show'])->middleware('permission:student.view');
        Route::put('/murid/{murid}', [\App\Modules\Student\Http\Controllers\Api\V2\MuridController::class, 'update'])->middleware('permission:student.update');
        Route::delete('/murid/{murid}', [\App\Modules\Student\Http\Controllers\Api\V2\MuridController::class, 'destroy'])->middleware('permission:student.delete');

        // Catalog (Jenjang, Program, Paket, Harga)
        Route::prefix('catalog')->group(function () {
            // Jenjang
            Route::get('jenjang/export', [\App\Modules\Catalog\Http\Controllers\Api\V2\JenjangController::class, 'export'])->middleware('permission:catalog.jenjang.view');
            Route::get('jenjang', [\App\Modules\Catalog\Http\Controllers\Api\V2\JenjangController::class, 'index'])->middleware('permission:catalog.jenjang.view');
            Route::post('jenjang', [\App\Modules\Catalog\Http\Controllers\Api\V2\JenjangController::class, 'store'])->middleware('permission:catalog.jenjang.create');
            Route::get('jenjang/{jenjang}', [\App\Modules\Catalog\Http\Controllers\Api\V2\JenjangController::class, 'show'])->middleware('permission:catalog.jenjang.view');
            Route::put('jenjang/{jenjang}', [\App\Modules\Catalog\Http\Controllers\Api\V2\JenjangController::class, 'update'])->middleware('permission:catalog.jenjang.update');
            Route::delete('jenjang/{jenjang}', [\App\Modules\Catalog\Http\Controllers\Api\V2\JenjangController::class, 'destroy'])->middleware('permission:catalog.jenjang.delete');

            // Program
            Route::get('program/export', [\App\Modules\Catalog\Http\Controllers\Api\V2\ProgramController::class, 'export'])->middleware('permission:catalog.program.view');
            Route::get('program', [\App\Modules\Catalog\Http\Controllers\Api\V2\ProgramController::class, 'index'])->middleware('permission:catalog.program.view');
            Route::post('program', [\App\Modules\Catalog\Http\Controllers\Api\V2\ProgramController::class, 'store'])->middleware('permission:catalog.program.create');
            Route::get('program/{program}', [\App\Modules\Catalog\Http\Controllers\Api\V2\ProgramController::class, 'show'])->middleware('permission:catalog.program.view');
            Route::put('program/{program}', [\App\Modules\Catalog\Http\Controllers\Api\V2\ProgramController::class, 'update'])->middleware('permission:catalog.program.update');
            Route::delete('program/{program}', [\App\Modules\Catalog\Http\Controllers\Api\V2\ProgramController::class, 'destroy'])->middleware('permission:catalog.program.delete');

            // Paket
            Route::get('paket/export', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketController::class, 'export'])->middleware('permission:catalog.paket.view');
            Route::get('paket', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketController::class, 'index'])->middleware('permission:catalog.paket.view');
            Route::post('paket', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketController::class, 'store'])->middleware('permission:catalog.paket.create');
            Route::get('paket/{paket}', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketController::class, 'show'])->middleware('permission:catalog.paket.view');
            Route::put('paket/{paket}', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketController::class, 'update'])->middleware('permission:catalog.paket.update');
            Route::delete('paket/{paket}', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketController::class, 'destroy'])->middleware('permission:catalog.paket.delete');

            // Paket Harga
            Route::get('harga/export', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'export'])->middleware('permission:catalog.pricing.view');
            Route::get('harga/lookup', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'lookup'])->middleware('permission:catalog.pricing.view');
            Route::get('harga', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'index'])->middleware('permission:catalog.pricing.view');
            Route::post('harga', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'store'])->middleware('permission:catalog.pricing.create');
            Route::get('harga/{harga}', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'show'])->middleware('permission:catalog.pricing.view');
            Route::put('harga/{harga}', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'update'])->middleware('permission:catalog.pricing.update');
            Route::delete('harga/{harga}', [\App\Modules\Catalog\Http\Controllers\Api\V2\PaketHargaController::class, 'destroy'])->middleware('permission:catalog.pricing.delete');
        });

        // Landing Gallery (admin CRUD)
        Route::get('/landing-gallery', [\App\Modules\Landing\Http\Controllers\Api\V2\LandingGalleryController::class, 'index'])->middleware('permission:landing.gallery.view');
        Route::post('/landing-gallery', [\App\Modules\Landing\Http\Controllers\Api\V2\LandingGalleryController::class, 'store'])->middleware('permission:landing.gallery.manage');
        Route::get('/landing-gallery/{landingGalleryItem}', [\App\Modules\Landing\Http\Controllers\Api\V2\LandingGalleryController::class, 'show'])->middleware('permission:landing.gallery.view');
        Route::put('/landing-gallery/{landingGalleryItem}', [\App\Modules\Landing\Http\Controllers\Api\V2\LandingGalleryController::class, 'update'])->middleware('permission:landing.gallery.manage');
        Route::patch('/landing-gallery/{landingGalleryItem}', [\App\Modules\Landing\Http\Controllers\Api\V2\LandingGalleryController::class, 'update'])->middleware('permission:landing.gallery.manage');
        Route::delete('/landing-gallery/{landingGalleryItem}', [\App\Modules\Landing\Http\Controllers\Api\V2\LandingGalleryController::class, 'destroy'])->middleware('permission:landing.gallery.manage');

        // Enrollments
        Route::get('/enrollments', [\App\Modules\Enrollment\Http\Controllers\EnrollmentController::class, 'index'])->middleware('permission:enrollment.view');
        Route::get('/enrollments/export', [\App\Modules\Enrollment\Http\Controllers\EnrollmentController::class, 'export'])->middleware('permission:enrollment.view');
        Route::post('/enrollments', [\App\Modules\Enrollment\Http\Controllers\EnrollmentController::class, 'store'])->middleware('permission:enrollment.create');
        Route::get('/enrollments/{enrollment}', [\App\Modules\Enrollment\Http\Controllers\EnrollmentController::class, 'show'])->middleware('permission:enrollment.view');
        Route::put('/enrollments/{enrollment}', [\App\Modules\Enrollment\Http\Controllers\EnrollmentController::class, 'update'])->middleware('permission:enrollment.update');
        Route::put('/enrollments/{enrollment}/registration-fee-status', [\App\Modules\Enrollment\Http\Controllers\EnrollmentController::class, 'updateRegistrationFeeStatus'])->middleware('permission:enrollment.update');
        Route::delete('/enrollments/{enrollment}', [\App\Modules\Enrollment\Http\Controllers\EnrollmentController::class, 'destroy'])->middleware('permission:enrollment.delete');

        // Paket Murid (Meetings & Credit)
        Route::get('/enrollments/{enrollment}/paket-murid', [\App\Modules\Enrollment\Http\Controllers\PaketMuridController::class, 'index'])
            ->middleware('permission:enrollment.view');
        Route::post('/enrollments/{enrollment}/paket-murid', [\App\Modules\Enrollment\Http\Controllers\PaketMuridController::class, 'store'])
            ->middleware('permission:paket_murid.create');
        Route::get('/paket-murid/ledger/all', [\App\Modules\Enrollment\Http\Controllers\PaketMuridController::class, 'getLedger'])
            ->middleware('permission:paket_murid.view');
        Route::get('/paket-murid/{paketMurid}/ledger', [\App\Modules\Enrollment\Http\Controllers\PaketMuridController::class, 'getLedger'])
            ->middleware('permission:paket_murid.view');
        Route::post('/paket-murid/{paketMurid}/adjust', [\App\Modules\Enrollment\Http\Controllers\PaketMuridController::class, 'adjust'])
            ->middleware('permission:paket_murid.update');

        // Enrollment Payment Actions (linked to Kas Transaksi)
        Route::post('/enrollments/{enrollment}/pay-registration-fee', [\App\Modules\Finance\Http\Controllers\KasTransaksiController::class, 'payRegistrationFee'])
            ->middleware('permission:enrollment.update');
        Route::post('/enrollments/{enrollment}/pay-package-topup', [\App\Modules\Finance\Http\Controllers\KasTransaksiController::class, 'payPackageTopup'])
            ->middleware('permission:paket_murid.create');
        Route::get('/enrollments/{enrollment}/saldo-dan-transaksi', [\App\Modules\Finance\Http\Controllers\KasTransaksiController::class, 'getSaldoAndTransactions'])
            ->middleware('permission:enrollment.view');

        // Kas Transaksi (Cash Transactions)
        Route::prefix('kas')->group(function () {
            Route::get('/transaksi', [\App\Modules\Finance\Http\Controllers\KasTransaksiController::class, 'index'])
                ->middleware('permission:kas.view');
            Route::post('/transaksi', [\App\Modules\Finance\Http\Controllers\KasTransaksiController::class, 'store'])
                ->middleware('permission:kas.create');
            Route::get('/transaksi/export', [\App\Modules\Finance\Http\Controllers\KasTransaksiController::class, 'export'])
                ->middleware('permission:kas.view');

            // Print routes
            Route::get('/transaksi/{kasTransaksi}/print-thermal', [\App\Modules\Finance\Http\Controllers\KasTransaksiPrintController::class, 'printThermal'])
                ->middleware('permission:kas.view');
            Route::get('/transaksi/{kasTransaksi}/download-receipt', [\App\Modules\Finance\Http\Controllers\KasTransaksiPrintController::class, 'downloadReceipt'])
                ->middleware('permission:kas.view');

            // Shift Management
            Route::get('/shift', [\App\Modules\Finance\Http\Controllers\KasShiftController::class, 'index'])
                ->middleware('permission:kas.view');
            Route::get('/shift/current', [\App\Modules\Finance\Http\Controllers\KasShiftController::class, 'current'])
                ->middleware('permission:kas.view');
            Route::post('/shift/open', [\App\Modules\Finance\Http\Controllers\KasShiftController::class, 'open'])
                ->middleware('permission:kas.create');
            Route::post('/shift/{id}/close', [\App\Modules\Finance\Http\Controllers\KasShiftController::class, 'close'])
                ->middleware('permission:kas.update');
            Route::get('/shift/{id}/summary', [\App\Modules\Finance\Http\Controllers\KasShiftController::class, 'summary'])
                ->middleware('permission:kas.view');
            Route::get('/shift/{id}/print', [\App\Modules\Finance\Http\Controllers\KasShiftController::class, 'print'])
                ->middleware('permission:kas.view');
        });

        // Kelas (Academic)
        Route::get('/kelas', [\App\Modules\Academic\Http\Controllers\KelasController::class, 'index'])->middleware('permission:kelas.view');
        Route::get('/kelas/export', [\App\Modules\Academic\Http\Controllers\KelasController::class, 'export'])->middleware('permission:kelas.view');
        Route::post('/kelas', [\App\Modules\Academic\Http\Controllers\KelasController::class, 'store'])->middleware('permission:kelas.create');
        Route::get('/kelas/{id}', [\App\Modules\Academic\Http\Controllers\KelasController::class, 'show'])->middleware('permission:kelas.view');
        Route::put('/kelas/{id}', [\App\Modules\Academic\Http\Controllers\KelasController::class, 'update'])->middleware('permission:kelas.update');
        Route::delete('/kelas/{id}', [\App\Modules\Academic\Http\Controllers\KelasController::class, 'destroy'])->middleware('permission:kelas.delete');

        // Kelas Members
        Route::get('/kelas/{id}/anggota', [\App\Modules\Academic\Http\Controllers\KelasAnggotaController::class, 'index'])->middleware('permission:kelas.view');
        Route::post('/kelas/{id}/anggota', [\App\Modules\Academic\Http\Controllers\KelasAnggotaController::class, 'store'])->middleware('permission:kelas.manage_members');
        Route::delete('/kelas/{id}/anggota/{enrollmentId}', [\App\Modules\Academic\Http\Controllers\KelasAnggotaController::class, 'destroy'])->middleware('permission:kelas.manage_members');
        // Academic Sessions (Jadwal Kelas & Sesi)
        // Jadwal Kelas
        Route::get('/kelas/{kelasId}/jadwal', [\App\Modules\AcademicSessions\Http\Controllers\JadwalKelasController::class, 'index'])->middleware('permission:schedule.manage');
        Route::post('/kelas/{kelasId}/jadwal', [\App\Modules\AcademicSessions\Http\Controllers\JadwalKelasController::class, 'store'])->middleware('permission:schedule.manage');

        // Sesi
        Route::get('/sesi/export', [\App\Modules\AcademicSessions\Http\Controllers\SesiController::class, 'export'])->middleware('permission:session.view');
        Route::get('/kelas/{kelasId}/sesi/export', [\App\Modules\AcademicSessions\Http\Controllers\SesiController::class, 'export'])->middleware('permission:session.view');
        Route::get('/kelas/{kelasId}/sesi', [\App\Modules\AcademicSessions\Http\Controllers\SesiController::class, 'indexByKelas'])->middleware('permission:session.view');
        Route::post('/kelas/{kelasId}/sesi/generate', [\App\Modules\AcademicSessions\Http\Controllers\SesiController::class, 'generate'])->middleware('permission:session.create');
        Route::get('/kelas/{kelasId}/logbook', [\App\Modules\AcademicSessions\Http\Controllers\SesiLogbookController::class, 'indexByKelas'])->middleware('permission:session.logbook.manage');

        Route::get('/sesi/{id}', [\App\Modules\AcademicSessions\Http\Controllers\SesiController::class, 'show'])->middleware('permission:session.view');
        Route::put('/sesi/{id}', [\App\Modules\AcademicSessions\Http\Controllers\SesiController::class, 'update'])->middleware('permission:session.update');
        Route::post('/sesi/{id}/sync-anggota', [\App\Modules\AcademicSessions\Http\Controllers\SesiController::class, 'syncAnggota'])->middleware('permission:session.attendance.manage');

        // Absensi
        Route::get('/sesi/{id}/absensi', [\App\Modules\AcademicSessions\Http\Controllers\SesiAbsensiController::class, 'index'])->middleware('permission:session.attendance.manage');
        Route::put('/sesi/{id}/absensi/bulk', [\App\Modules\AcademicSessions\Http\Controllers\SesiAbsensiController::class, 'bulkUpdate'])->middleware('permission:session.attendance.manage');
        Route::post('/sesi/{id}/absensi/move', [\App\Modules\AcademicSessions\Http\Controllers\SesiAbsensiController::class, 'moveAttendance'])->middleware('permission:session.attendance.manage');

        // Logbook
        Route::get('/sesi/{id}/logbook', [\App\Modules\AcademicSessions\Http\Controllers\SesiLogbookController::class, 'show'])->middleware('permission:session.logbook.manage');
        Route::put('/sesi/{id}/logbook', [\App\Modules\AcademicSessions\Http\Controllers\SesiLogbookController::class, 'upsert'])->middleware('permission:session.logbook.manage');

        Route::get('/sesi/{id}/logbook-murid', [\App\Modules\AcademicSessions\Http\Controllers\SesiLogbookController::class, 'showStudent'])->middleware('permission:session.logbook.manage');
        Route::put('/sesi/{id}/logbook-murid/bulk', [\App\Modules\AcademicSessions\Http\Controllers\SesiLogbookController::class, 'bulkUpsertStudent'])->middleware('permission:session.logbook.manage');

        Route::get('/murid/{muridId}/logbook', [\App\Modules\AcademicSessions\Http\Controllers\SesiLogbookController::class, 'indexByStudent'])->middleware('permission:session.logbook.manage');

        // Materi Modul (Learning Materials)
        Route::get('/materi-modul', [\App\Modules\Learning\Http\Controllers\MateriModulController::class, 'index'])->middleware('permission:materials.view');
        Route::post('/materi-modul', [\App\Modules\Learning\Http\Controllers\MateriModulController::class, 'store'])->middleware('permission:materials.create');
        Route::get('/materi-modul/{id}', [\App\Modules\Learning\Http\Controllers\MateriModulController::class, 'show'])->middleware('permission:materials.view');
        Route::put('/materi-modul/{id}', [\App\Modules\Learning\Http\Controllers\MateriModulController::class, 'update'])->middleware('permission:materials.update');
        Route::delete('/materi-modul/{id}', [\App\Modules\Learning\Http\Controllers\MateriModulController::class, 'destroy'])->middleware('permission:materials.delete');

        // Materi Modul Items
        Route::get('/materi-modul/{id}/items', [\App\Modules\Learning\Http\Controllers\MateriModulItemController::class, 'index'])->middleware('permission:materials.view');
        Route::post('/materi-modul/{id}/items', [\App\Modules\Learning\Http\Controllers\MateriModulItemController::class, 'store'])->middleware('permission:materials.create');
        Route::put('/materi-modul/items/{itemId}', [\App\Modules\Learning\Http\Controllers\MateriModulItemController::class, 'update'])->middleware('permission:materials.update');
        Route::delete('/materi-modul/items/{itemId}', [\App\Modules\Learning\Http\Controllers\MateriModulItemController::class, 'destroy'])->middleware('permission:materials.delete');
        Route::post('/materi-modul/{id}/reorder', [\App\Modules\Learning\Http\Controllers\MateriModulItemController::class, 'reorder'])->middleware('permission:materials.update');
        Route::post('/materi-modul/items/{itemId}/toggle-status', [\App\Modules\Learning\Http\Controllers\MateriModulItemController::class, 'toggleStatus'])->middleware('permission:materials.update');
        Route::get('/materi-modul/items/{itemId}/download', [\App\Modules\Learning\Http\Controllers\MateriModulItemController::class, 'download'])->middleware('permission:materials.view');

        // Assignments (Tugas)
        Route::get('/assignments', [\App\Modules\Learning\Http\Controllers\AssignmentController::class, 'index'])->middleware('permission:assignment.view');
        Route::post('/assignments', [\App\Modules\Learning\Http\Controllers\AssignmentController::class, 'store'])->middleware('permission:assignment.create');
        Route::get('/assignments/{id}', [\App\Modules\Learning\Http\Controllers\AssignmentController::class, 'show'])->middleware('permission:assignment.view');
        Route::put('/assignments/{id}', [\App\Modules\Learning\Http\Controllers\AssignmentController::class, 'update'])->middleware('permission:assignment.update');
        Route::post('/assignments/{id}/close', [\App\Modules\Learning\Http\Controllers\AssignmentController::class, 'close'])->middleware('permission:assignment.manage');

        // Assignment Submissions
        Route::get('/assignments/{id}/submissions', [\App\Modules\Learning\Http\Controllers\AssignmentSubmissionController::class, 'index'])->middleware('permission:assignment.view');
        Route::post('/assignments/{id}/submissions', [\App\Modules\Learning\Http\Controllers\AssignmentSubmissionController::class, 'store'])->middleware('permission:assignment.create');
        Route::put('/assignments/submissions/{submissionId}/review', [\App\Modules\Learning\Http\Controllers\AssignmentSubmissionController::class, 'review'])->middleware('permission:assignment.manage');

        // Sesi Materi & Assignment Management
        Route::get('/sesi/{sesiId}/materi-assignments', [\App\Modules\Learning\Http\Controllers\SesiMateriAssignmentController::class, 'index']); // TODO: Add back ->middleware('permission:session.view')
        Route::post('/sesi/{sesiId}/assign-materi', [\App\Modules\Learning\Http\Controllers\SesiMateriAssignmentController::class, 'assignMateri'])->middleware('permission:session.update');
        Route::delete('/sesi/{sesiId}/materi/{materiId}', [\App\Modules\Learning\Http\Controllers\SesiMateriAssignmentController::class, 'unassignMateri'])->middleware('permission:session.update');
        Route::post('/sesi/{sesiId}/assign-assignment', [\App\Modules\Learning\Http\Controllers\SesiMateriAssignmentController::class, 'assignAssignment'])->middleware('permission:session.update');
        Route::delete('/sesi/{sesiId}/assignment/{assignmentId}', [\App\Modules\Learning\Http\Controllers\SesiMateriAssignmentController::class, 'unassignAssignment'])->middleware('permission:session.update');

        // Student Portal
        Route::get('/murid/my-materi', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'getMyMateri']);
        Route::get('/murid/my-assignments', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'getMyAssignments']);
        Route::post('/murid/materi/{materiAssignmentId}/mark-accessed', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'markMateriAccessed']);
        Route::get('/murid/progress', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'getProgress']);
    });
});

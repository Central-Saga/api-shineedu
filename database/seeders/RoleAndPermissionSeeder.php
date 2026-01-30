<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Pastikan tidak dobel saat re-seed
        // (optional) Permission::query()->delete(); Role::query()->delete();

        $modules = [
            // Identity & Security
            'users',
            'roles',
            'permissions',

            // Core Bimbel
            'catalog',        // paket les, program, subject
            'catalog.jenjang',
            'catalog.program',
            'catalog.paket',
            'catalog.pricing',
            'kelas',          // kelas akademik
            'scheduling',     // jadwal, reschedule rules
            'schedule',       // FIX: route uses 'schedule.manage'
            'enrollment',     // sesi, carry over, paket berjalan
            'attendance',     // absensi guru/murid
            'logbook',        // catatan sesi / laporan guru
            'session',        // NEW: sesi kelas
            'session.attendance', // NEW: absensi sesi
            'session.logbook',    // NEW: logbook sesi
            'materials',      // materi upload
            'assessments',    // penilaian/sertifikat
            'student',        // data murid
            'student.view',   // view murid
            'student.create', // create murid
            'student.update', // update murid
            'student.delete', // delete murid
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            'enrollment.delete',
            'paket_murid',    // NEW: saldo pertemuan / meeting balance

            // Finance & Ops
            'invoices',       // invoice & pembayaran
            'payments',
            'kas',            // NEW: transaksi kas (uang masuk/keluar)
            'reports',
            'documents',      // dokumen & upload umum
            'notifications',
            'activity_logs',
            'settings',

            // HR
            'employees',      // karyawan
            'vacancies',      // lowongan
            'applications',   // lamaran
            'cuti',           // cuti/izin/sakit
            'absensi',        // kehadiran manual/mesin
            'pengaturan_cuti', // setting kuota cuti
            'rekap_bulanan',
            'gaji',

            // Landing (gallery for landing page)
            'landing.gallery',
        ];

        // Pola CRUD umum per modul
        $actions = ['view', 'create', 'update', 'delete', 'manage'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web', // samakan dengan guard kamu
                ]);
            }
        }

        // No extra permissions needed - all follow standard CRUD pattern

        // Roles
        $superadmin = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $teacher    = Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web']);
        $student    = Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);

        // Superadmin: semua
        $superadmin->syncPermissions(Permission::all());

        // Admin: semua kecuali role/permission critical (opsional)
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'roles.manage',
                'roles.delete',
                'permissions.manage',
                'permissions.delete',
            ])->get()
        );

        // Teacher: fokus ke jadwal, absensi, logbook, materi, assessments (view/manage terbatas)
        $teacher->syncPermissions([
            'scheduling.view',
            'scheduling.manage',
            'schedule.manage', // FIX
            'attendance.view',
            'attendance.manage',
            'logbook.view',
            'logbook.manage',
            // Sesi Permissions
            'session.view',
            'session.create',
            'session.update',
            'session.attendance.manage',
            'session.logbook.manage',

            'materials.view',
            'materials.manage',
            'assessments.view',
            'assessments.manage',
            'enrollment.view',
            'kelas.view', // Guru liat kelas
            'catalog.view',
            'users.view', // kalau guru boleh lihat profil murid tertentu, nanti bisa refine policy
            'cuti.view',
            'cuti.create',
            'cuti.update',
            'cuti.delete',
        ]);

        // Student: mostly view
        $student->syncPermissions([
            'scheduling.view',
            'attendance.view',
            'logbook.view',
            'materials.view',
            'assessments.view',
            'enrollment.view',
            'kelas.view', // Murid liat kelasnya
            'catalog.view',
        ]);
    }
}

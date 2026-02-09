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
            'jadwal_kerja',   // jadwal kerja (API v2)
            'realisasi_jadwal_kerja', // realisasi jadwal (API v2)
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
            'assignment',     // NEW: tugas murid

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
            'job_application', // lamaran kerja (job applications)
            'job_vacancy',     // lowongan kerja (job vacancies)
            'cuti',           // cuti/izin/sakit
            'absensi',        // kehadiran manual/mesin
            'pengaturan_cuti', // setting kuota cuti
            'rekap_bulanan',
            'gaji',

            // Landing (gallery & blog for landing page)
            'landing.gallery',

            // Blog (content for landing blog)
            'blog',
            'landing.blog',
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
            // Dashboard / General
            'scheduling.view',
            'scheduling.manage',
            'schedule.manage',

            // Academic Session & Attendance
            'session.view',
            'session.create',
            'session.update',
            'session.attendance.manage',
            'session.logbook.manage',

            // Materials & Assignments
            'materials.view',
            'materials.create',
            'materials.update',
            'materials.manage',
            'assignment.view',
            'assignment.create',
            'assignment.update',
            'assignment.manage',
            'assessments.view',
            'assessments.manage',

            // Student Data
            'student.view',
            'paket_murid.view', // view saldo murid

            // Operations
            'enrollment.view',
            'kelas.view',

            // HR Self-Service
            'cuti.view',
            'cuti.create',
            'cuti.update', // maybe view only depending on flow
            'absensi.view', // view own attendance history
            'absensi.create', // perform attendance (check-in/out)

            // Scheduling (Jadwal Kerja & Realisasi)
            'jadwal_kerja.view',
            'realisasi_jadwal_kerja.view',
        ]);

        // Student: mostly view + submit assignments
        $student->syncPermissions([
            // View Schedule & Sessions
            'scheduling.view',
            'session.view',

            // Content
            'materials.view',
            'assignment.view',
            'assignment.create', // submit assignment
            'assessments.view',

            // Academic Data
            'enrollment.view',
            'kelas.view',
            'paket_murid.view', // view own packet balance

            // View Catalog
            'catalog.view',
        ]);
    }
}

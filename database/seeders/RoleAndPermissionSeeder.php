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
            'scheduling',     // jadwal, reschedule rules
            'enrollment',     // sesi, carry over, paket berjalan
            'attendance',     // absensi guru/murid
            'logbook',        // catatan sesi / laporan guru
            'materials',      // materi upload
            'assessments',    // penilaian/sertifikat

            // Finance & Ops
            'invoices',       // invoice & pembayaran
            'payments',
            'payroll',        // payroll guru + potongan
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
            'jadwal_kerja',
            'realisasi_jadwal_kerja',
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
            'attendance.view',
            'attendance.manage',
            'logbook.view',
            'logbook.manage',
            'materials.view',
            'materials.manage',
            'assessments.view',
            'assessments.manage',
            'enrollment.view',
            'catalog.view',
            'users.view', // kalau guru boleh lihat profil murid tertentu, nanti bisa refine policy
            'cuti.view',
            'cuti.create',
            'absensi.view',
            'jadwal_kerja.view',
            'realisasi_jadwal_kerja.view',
            'realisasi_jadwal_kerja.manage',
        ]);

        // Student: mostly view
        $student->syncPermissions([
            'scheduling.view',
            'attendance.view',
            'logbook.view',
            'materials.view',
            'assessments.view',
            'enrollment.view',
            'catalog.view',
        ]);
    }
}

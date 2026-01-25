<?php

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            // TK
            ['kode' => 'PROGRAM_TK_BHS_INGGRIS', 'nama' => 'Bahasa Inggris TK'],
            ['kode' => 'PROGRAM_TK_CALISTUNG', 'nama' => 'Calistung (Baca, Tulis & Hitung)'],
            ['kode' => 'PROGRAM_TK_CODING', 'nama' => 'Coding TK'],

            // SD
            ['kode' => 'PROGRAM_SD_BHS_INGGRIS', 'nama' => 'Bahasa Inggris SD'],
            ['kode' => 'PROGRAM_SD_MATEMATIKA', 'nama' => 'Matematika SD'],
            ['kode' => 'PROGRAM_SD_KURMER', 'nama' => 'Kurikulum Merdeka (Semua Mapel) SD'],
            ['kode' => 'PROGRAM_SD_BHS_BALI', 'nama' => 'Bahasa Bali SD'],
            ['kode' => 'PROGRAM_SD_KOMPUTER', 'nama' => 'Komputer SD'],
            ['kode' => 'PROGRAM_SD_CODING', 'nama' => 'Coding SD'],
            ['kode' => 'PROGRAM_SD_TKA', 'nama' => 'TKA SD'],

            // SMP
            ['kode' => 'PROGRAM_SMP_BHS_INGGRIS', 'nama' => 'Bahasa Inggris SMP'],
            ['kode' => 'PROGRAM_SMP_MATEMATIKA', 'nama' => 'Matematika SMP'],
            ['kode' => 'PROGRAM_SMP_IPA', 'nama' => 'IPA SMP'],
            ['kode' => 'PROGRAM_SMP_IPS', 'nama' => 'IPS SMP'],
            ['kode' => 'PROGRAM_SMP_KOMPUTER', 'nama' => 'Komputer SMP'],
            ['kode' => 'PROGRAM_SMP_CODING', 'nama' => 'Coding SMP'],
            ['kode' => 'PROGRAM_SMP_TKA', 'nama' => 'TKA SMP'],

            // SMA
            ['kode' => 'PROGRAM_SMAK_BHS_INGGRIS', 'nama' => 'Bahasa Inggris SMA/K'],
            ['kode' => 'PROGRAM_SMAK_MATEMATIKA', 'nama' => 'Matematika SMA/K'],
            ['kode' => 'PROGRAM_SMAK_FISIKA', 'nama' => 'Fisika SMA/K'],
            ['kode' => 'PROGRAM_SMAK_KIMIA', 'nama' => 'Kimia SMA/K'],
            ['kode' => 'PROGRAM_SMAK_BIOLOGI', 'nama' => 'Biologi SMA/K'],
            ['kode' => 'PROGRAM_SMAK_EKO_AKUN', 'nama' => 'Ekonomi/Akuntansi SMA/K'],
            ['kode' => 'PROGRAM_SMAK_KOMPUTER', 'nama' => 'Komputer SMA/K'],
            ['kode' => 'PROGRAM_SMAK_CODING', 'nama' => 'Coding SMA/K'],
            ['kode' => 'PROGRAM_SMAK_TKA', 'nama' => 'TKA SMA/K'],

            // UMUM
            ['kode' => 'PROGRAM_UMUM_BHS_INGGRIS', 'nama' => 'Bahasa Inggris Umum'],
            ['kode' => 'PROGRAM_UMUM_CPNS', 'nama' => 'Persiapan CPNS'],
            ['kode' => 'PROGRAM_UMUM_KOMPUTER', 'nama' => 'Komputer Umum'],
            ['kode' => 'PROGRAM_UMUM_CODING', 'nama' => 'Coding Umum'],
            ['kode' => 'PROGRAM_UMUM_MANDARIN', 'nama' => 'Bahasa Mandarin'],

            // Bundle
            ['kode' => 'PROGRAM_CERMAT', 'nama' => 'Paket Cermat'],
        ];

        foreach ($programs as $data) {
            Program::withTrashed()->updateOrCreate(
                ['kode' => $data['kode']],
                [
                    'nama' => $data['nama'],
                    'status' => 'Aktif',
                    'deleted_at' => null,
                ]
            );
        }
    }
}

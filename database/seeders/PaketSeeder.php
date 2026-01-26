<?php

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Paket;
use Illuminate\Database\Seeder;

class PaketSeeder extends Seeder
{
    public function run(): void
    {
        $pakets = [
            // Standard Reguler
            [
                'kode' => 'REGULER_4X',
                'nama' => 'Reguler 4 Pertemuan',
                'tipe' => 'REGULER',
                'pertemuan_per_bulan' => 4,
                'durasi_menit' => 70,
            ],
            [
                'kode' => 'REGULER_8X',
                'nama' => 'Reguler 8 Pertemuan',
                'tipe' => 'REGULER',
                'pertemuan_per_bulan' => 8,
                'durasi_menit' => 70,
            ],
            // Standard Private
            [
                'kode' => 'PRIVATE_4X',
                'nama' => 'Private 4 Pertemuan',
                'tipe' => 'PRIVATE',
                'pertemuan_per_bulan' => 4,
                'durasi_menit' => 70,
            ],
            [
                'kode' => 'PRIVATE_8X',
                'nama' => 'Private 8 Pertemuan',
                'tipe' => 'PRIVATE',
                'pertemuan_per_bulan' => 8,
                'durasi_menit' => 70,
            ],
            // Computer Specialized
            [
                'kode' => 'KOMPUTER_REGULER_4X',
                'nama' => 'Komputer Reguler 4 Pertemuan',
                'tipe' => 'REGULER',
                'pertemuan_per_bulan' => 4,
                'durasi_menit' => 90,
                'bisa_tambah_pertemuan' => true,
            ],
            [
                'kode' => 'KOMPUTER_PRIVATE_4X',
                'nama' => 'Komputer Private 4 Pertemuan',
                'tipe' => 'PRIVATE',
                'pertemuan_per_bulan' => 4,
                'durasi_menit' => 90,
            ],
            // TKA Specialized
            [
                'kode' => 'TKA_PRIVATE_4X_MAX1',
                'nama' => 'TKA Private 4 Pertemuan (Max 1 Mapel)',
                'tipe' => 'PRIVATE',
                'pertemuan_per_bulan' => 4,
                'durasi_menit' => 70,
                'boleh_mix_mapel' => true,
                'max_mapel' => 1,
            ],
            [
                'kode' => 'TKA_PRIVATE_8X_MAX2',
                'nama' => 'TKA Private 8 Pertemuan (Max 2 Mapel)',
                'tipe' => 'PRIVATE',
                'pertemuan_per_bulan' => 8,
                'durasi_menit' => 70,
                'boleh_mix_mapel' => true,
                'max_mapel' => 2,
            ],
            // Mandarin Specialized
            [
                'kode' => 'MANDARIN_PRIVATE_4X',
                'nama' => 'Mandarin Private 4 Pertemuan',
                'tipe' => 'PRIVATE',
                'pertemuan_per_bulan' => 4,
                'durasi_menit' => 70,
            ],
            // Coding Specialized
            [
                'kode' => 'CODING_PRIVATE',
                'nama' => 'Coding Private',
                'tipe' => 'PRIVATE',
                'pertemuan_per_bulan' => 4, // Assumed 4x based on typical course structure
                'durasi_menit' => 90, // Assumed 90m for coding courses
            ],
            // Bundle
            [
                'kode' => 'BUNDLE_CERMAT_3MAPEL',
                'nama' => 'Paket Cermat (3 Mapel)',
                'tipe' => 'BUNDLE',
                'max_mapel' => 3,
                'pertemuan_per_bulan' => 12, // Usually 3 mapel x 4 meet
                'boleh_mix_mapel' => true,
            ],
        ];

        foreach ($pakets as $data) {
            Paket::withTrashed()->updateOrCreate(
                ['kode' => $data['kode']],
                array_merge($data, [
                    'status' => 'Aktif',
                    'deleted_at' => null,
                    'bisa_tambah_pertemuan' => $data['bisa_tambah_pertemuan'] ?? true,
                    'bisa_ganti_hari' => true,
                    'boleh_mix_mapel' => $data['boleh_mix_mapel'] ?? false,
                ])
            );
        }
    }
}

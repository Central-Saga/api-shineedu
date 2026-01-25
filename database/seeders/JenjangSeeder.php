<?php

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Jenjang;
use Illuminate\Database\Seeder;

class JenjangSeeder extends Seeder
{
    public function run(): void
    {
        $jenjangs = [
            ['kode' => 'JENJANG_TK', 'nama' => 'TK'],
            ['kode' => 'JENJANG_SD', 'nama' => 'SD'],
            ['kode' => 'JENJANG_SMP', 'nama' => 'SMP'],
            ['kode' => 'JENJANG_SMAK', 'nama' => 'SMA/K'],
            ['kode' => 'JENJANG_UMUM', 'nama' => 'UMUM'],
        ];

        foreach ($jenjangs as $data) {
            Jenjang::withTrashed()->updateOrCreate(
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

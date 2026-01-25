<?php

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Catalog\Domain\Models\Program;
use Illuminate\Database\Seeder;

class ProgramJenjangSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            // TK
            'PROGRAM_TK_BHS_INGGRIS' => ['JENJANG_TK'],
            'PROGRAM_TK_CALISTUNG' => ['JENJANG_TK'],
            'PROGRAM_TK_CODING' => ['JENJANG_TK'],

            // SD
            'PROGRAM_SD_BHS_INGGRIS' => ['JENJANG_SD'],
            'PROGRAM_SD_MATEMATIKA' => ['JENJANG_SD'],
            'PROGRAM_SD_KURMER' => ['JENJANG_SD'],
            'PROGRAM_SD_BHS_BALI' => ['JENJANG_SD'],
            'PROGRAM_SD_KOMPUTER' => ['JENJANG_SD'],
            'PROGRAM_SD_CODING' => ['JENJANG_SD'],
            'PROGRAM_SD_TKA' => ['JENJANG_SD'],

            // SMP
            'PROGRAM_SMP_BHS_INGGRIS' => ['JENJANG_SMP'],
            'PROGRAM_SMP_MATEMATIKA' => ['JENJANG_SMP'],
            'PROGRAM_SMP_IPA' => ['JENJANG_SMP'],
            'PROGRAM_SMP_IPS' => ['JENJANG_SMP'],
            'PROGRAM_SMP_KOMPUTER' => ['JENJANG_SMP'],
            'PROGRAM_SMP_CODING' => ['JENJANG_SMP'],
            'PROGRAM_SMP_TKA' => ['JENJANG_SMP'],

            // SMA
            'PROGRAM_SMAK_BHS_INGGRIS' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_MATEMATIKA' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_FISIKA' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_KIMIA' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_BIOLOGI' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_EKO_AKUN' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_KOMPUTER' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_CODING' => ['JENJANG_SMAK'],
            'PROGRAM_SMAK_TKA' => ['JENJANG_SMAK'],

            // UMUM
            'PROGRAM_UMUM_BHS_INGGRIS' => ['JENJANG_UMUM'],
            'PROGRAM_UMUM_CPNS' => ['JENJANG_UMUM'],
            'PROGRAM_UMUM_KOMPUTER' => ['JENJANG_UMUM'],
            'PROGRAM_UMUM_CODING' => ['JENJANG_UMUM'],

            // Special Mapping: Mandarin SD-UMUM
            'PROGRAM_UMUM_MANDARIN' => ['JENJANG_SD', 'JENJANG_SMP', 'JENJANG_SMAK', 'JENJANG_UMUM'],

            // Special Mapping: Cermat SD-SMAK
            'PROGRAM_CERMAT' => ['JENJANG_SD', 'JENJANG_SMP', 'JENJANG_SMAK'],
        ];

        foreach ($mapping as $progKode => $jenjangKodes) {
            $program = Program::where('kode', $progKode)->first();
            if (!$program) continue;

            $jenjangIds = Jenjang::whereIn('kode', $jenjangKodes)->pluck('id')->toArray();
            $program->jenjangs()->sync($jenjangIds);
        }
    }
}

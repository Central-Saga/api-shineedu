<?php

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Catalog\Domain\Models\PaketHarga;
use App\Modules\Catalog\Domain\Models\Program;
use Illuminate\Database\Seeder;

class PaketHargaSeeder extends Seeder
{
    protected $effectiveFrom = '2026-01-01';

    public function run(): void
    {
        // Fetch references
        $jenjangs = Jenjang::all()->keyBy('kode');
        $programs = Program::all()->keyBy('kode');
        $pakets = Paket::all()->keyBy('kode');

        // 1. General Pricing (Reguler & Private per Level)
        $this->seedGeneralPrices($programs, $jenjangs, $pakets);

        // 2. Paket Cermat (Bundle)
        $this->seedCermatPrices($programs, $jenjangs, $pakets);

        // 3. TKA specialized pricing
        $this->seedTKAPrices($programs, $jenjangs, $pakets);

        // 4. Mandarin specialized pricing
        $this->seedMandarinPrices($programs, $jenjangs, $pakets);

        // 5. Komputer specialized pricing
        $this->seedKomputerPrices($programs, $jenjangs, $pakets);

        // 6. Coding specialized pricing
        $this->seedCodingPrices($programs, $jenjangs, $pakets);
    }

    protected function seedGeneralPrices($progs, $jjs, $pkts)
    {
        // Group general programs by Level
        $generalProgMapping = [
            'JENJANG_TK' => ['PROGRAM_TK_BHS_INGGRIS', 'PROGRAM_TK_CALISTUNG', 'PROGRAM_TK_CODING'],
            'JENJANG_SD' => ['PROGRAM_SD_BHS_INGGRIS', 'PROGRAM_SD_MATEMATIKA', 'PROGRAM_SD_KURMER', 'PROGRAM_SD_BHS_BALI'],
            'JENJANG_SMP' => ['PROGRAM_SMP_BHS_INGGRIS', 'PROGRAM_SMP_MATEMATIKA', 'PROGRAM_SMP_IPA', 'PROGRAM_SMP_IPS'],
            'JENJANG_SMAK' => ['PROGRAM_SMAK_BHS_INGGRIS', 'PROGRAM_SMAK_MATEMATIKA', 'PROGRAM_SMAK_FISIKA', 'PROGRAM_SMAK_KIMIA', 'PROGRAM_SMAK_BIOLOGI', 'PROGRAM_SMAK_EKO_AKUN'],
            'JENJANG_UMUM' => ['PROGRAM_UMUM_BHS_INGGRIS', 'PROGRAM_UMUM_CPNS'],
        ];

        // Specific rates per Level
        $rates = [
            'JENJANG_TK' => [
                'REG_4X' => 65000,
                'REG_8X' => 125000,
                'PVT_4X' => [1 => 165000, 2 => 145000, 3 => 125000, 4 => 105000, 5 => 95000, 6 => 80000],
                'PVT_8X' => [1 => 325000, 2 => 285000, 3 => 245000, 4 => 205000, 5 => 185000, 6 => 135000],
            ],
            'JENJANG_SD' => [
                'REG_4X' => 55000,
                'REG_8X' => 100000,
                'PVT_4X' => [1 => 160000, 2 => 140000, 3 => 120000, 4 => 100000, 5 => 90000, 6 => 75000],
                'PVT_8X' => [1 => 320000, 2 => 280000, 3 => 240000, 4 => 200000, 5 => 180000, 6 => 130000],
            ],
            'JENJANG_SMP' => [
                'REG_4X' => 55000,
                'REG_8X' => 100000,
                'PVT_4X' => [1 => 160000, 2 => 140000, 3 => 120000, 4 => 100000, 5 => 90000, 6 => 75000],
                'PVT_8X' => [1 => 320000, 2 => 280000, 3 => 240000, 4 => 200000, 5 => 180000, 6 => 130000],
            ],
            'JENJANG_SMAK' => [
                'REG_4X' => 65000,
                'REG_8X' => 125000,
                'PVT_4X' => [1 => 175000, 2 => 150000, 3 => 130000, 4 => 110000, 5 => 100000, 6 => 75000],
                'PVT_8X' => [1 => 350000, 2 => 300000, 3 => 260000, 4 => 220000, 5 => 200000, 6 => 150000],
            ],
            'JENJANG_UMUM' => [
                'REG_4X' => 100000,
                'REG_8X' => 200000,
                'PVT_4X' => [1 => 175000, 2 => 150000, 3 => 130000, 4 => 110000, 5 => 100000, 6 => 75000],
                'PVT_8X' => [1 => 350000, 2 => 300000, 3 => 260000, 4 => 220000, 5 => 200000, 6 => 150000],
            ],
        ];

        foreach ($generalProgMapping as $jjKode => $progKodes) {
            $jj = $jjs[$jjKode] ?? null;
            if (!$jj) continue;

            $jjRates = $rates[$jjKode];

            foreach ($progKodes as $progKode) {
                $prog = $progs[$progKode] ?? null;
                if (!$prog) continue;

                // Seed Reguler 4x
                $pktReg4 = $pkts['REGULER_4X'] ?? null;
                if ($pktReg4) $this->upsertPrice($prog->id, $jj->id, $pktReg4->id, 1, 1, $jjRates['REG_4X']);

                // Seed Reguler 8x
                $pktReg8 = $pkts['REGULER_8X'] ?? null;
                if ($pktReg8) $this->upsertPrice($prog->id, $jj->id, $pktReg8->id, 1, 1, $jjRates['REG_8X']);

                // Seed Private 4x (Tiers)
                $pktPvt4 = $pkts['PRIVATE_4X'] ?? null;
                if ($pktPvt4) {
                    foreach ($jjRates['PVT_4X'] as $count => $harga) {
                        $this->upsertPrice($prog->id, $jj->id, $pktPvt4->id, $count, ($count >= 6 ? null : $count), $harga);
                    }
                }

                // Seed Private 8x (Tiers)
                $pktPvt8 = $pkts['PRIVATE_8X'] ?? null;
                if ($pktPvt8) {
                    foreach ($jjRates['PVT_8X'] as $count => $harga) {
                        $this->upsertPrice($prog->id, $jj->id, $pktPvt8->id, $count, ($count >= 6 ? null : $count), $harga);
                    }
                }
            }
        }
    }

    protected function seedCermatPrices($progs, $jjs, $pkts)
    {
        $prog = $progs['PROGRAM_CERMAT'] ?? null;
        if (!$prog) return;

        $pkt = $pkts['BUNDLE_CERMAT_3MAPEL'] ?? null;
        if (!$pkt) return;

        $prices = [
            'JENJANG_SD' => 150000,
            'JENJANG_SMP' => 165000,
            'JENJANG_SMAK' => 195000,
        ];

        foreach ($prices as $jjKode => $harga) {
            $jj = $jjs[$jjKode] ?? null;
            if ($jj) $this->upsertPrice($prog->id, $jj->id, $pkt->id, 1, 1, $harga);
        }
    }

    protected function seedTKAPrices($progs, $jjs, $pkts)
    {
        $tkaProgKodes = ['PROGRAM_SD_TKA', 'PROGRAM_SMP_TKA', 'PROGRAM_SMAK_TKA'];
        $rates = [
            '4X' => [1 => 185000, 2 => 160000, 3 => 140000, 4 => 110000, 5 => 100000, 6 => 85000],
            '8X' => [1 => 365000, 2 => 315000, 3 => 280000, 4 => 220000, 5 => 200000, 6 => 145000],
        ];

        foreach ($tkaProgKodes as $progKode) {
            $prog = $progs[$progKode] ?? null;
            if (!$prog) continue;

            $jjKode = str_replace('PROGRAM_', 'JENJANG_', explode('_TKA', $progKode)[0]);
            $jj = $jjs[$jjKode] ?? null;
            if (!$jj) continue;

            // 4x Max 1
            $pkt4 = $pkts['TKA_PRIVATE_4X_MAX1'] ?? null;
            if ($pkt4) {
                foreach ($rates['4X'] as $count => $harga) {
                    $this->upsertPrice($prog->id, $jj->id, $pkt4->id, $count, ($count >= 6 ? null : $count), $harga);
                }
            }

            // 8x Max 2
            $pkt8 = $pkts['TKA_PRIVATE_8X_MAX2'] ?? null;
            if ($pkt8) {
                foreach ($rates['8X'] as $count => $harga) {
                    $this->upsertPrice($prog->id, $jj->id, $pkt8->id, $count, ($count >= 6 ? null : $count), $harga);
                }
            }
        }
    }

    protected function seedMandarinPrices($progs, $jjs, $pkts)
    {
        $prog = $progs['PROGRAM_UMUM_MANDARIN'] ?? null;
        if (!$prog) return;

        $pkt = $pkts['MANDARIN_PRIVATE_4X'] ?? null;
        if (!$pkt) return;

        $tiers = [1 => 260000, 2 => 230000, 3 => 200000, 4 => 160000, 5 => 120000, 6 => 100000];
        $jjKodes = ['JENJANG_SD', 'JENJANG_SMP', 'JENJANG_SMAK', 'JENJANG_UMUM'];

        foreach ($jjKodes as $jjKode) {
            $jj = $jjs[$jjKode] ?? null;
            if (!$jj) continue;

            foreach ($tiers as $count => $harga) {
                $this->upsertPrice($prog->id, $jj->id, $pkt->id, $count, ($count >= 6 ? null : $count), $harga);
            }
        }
    }

    protected function seedKomputerPrices($progs, $jjs, $pkts)
    {
        // Program Komputer per Level
        $mapping = [
            'JENJANG_SD' => ['prog' => 'PROGRAM_SD_KOMPUTER', 'reg' => 125000, 'pvt' => 250000],
            'JENJANG_SMP' => ['prog' => 'PROGRAM_SMP_KOMPUTER', 'reg' => 125000, 'pvt' => 250000],
            'JENJANG_SMAK' => ['prog' => 'PROGRAM_SMAK_KOMPUTER', 'reg' => 150000, 'pvt' => 300000],
            'JENJANG_UMUM' => ['prog' => 'PROGRAM_UMUM_KOMPUTER', 'reg' => 175000, 'pvt' => 350000],
        ];

        $pktReg = $pkts['KOMPUTER_REGULER_4X'] ?? null;
        $pktPvt = $pkts['KOMPUTER_PRIVATE_4X'] ?? null;

        foreach ($mapping as $jjKode => $setup) {
            $jj = $jjs[$jjKode] ?? null;
            $prog = $progs[$setup['prog']] ?? null;
            if (!$jj || !$prog) continue;

            if ($pktReg) $this->upsertPrice($prog->id, $jj->id, $pktReg->id, 1, 1, $setup['reg']);
            if ($pktPvt) $this->upsertPrice($prog->id, $jj->id, $pktPvt->id, 1, 1, $setup['pvt']);
        }
    }

    protected function seedCodingPrices($progs, $jjs, $pkts)
    {
        $pkt = $pkts['CODING_PRIVATE'] ?? null;
        if (!$pkt) return;

        // Group 1: SD & SMP
        $group1Tiers = [1 => 320000, 2 => 280000, 3 => 240000, 4 => 200000, 5 => 180000];
        foreach (['JENJANG_SD', 'JENJANG_SMP'] as $jjKode) {
            $jj = $jjs[$jjKode] ?? null;
            $progKode = str_replace('JENJANG_', 'PROGRAM_', $jjKode) . '_CODING';
            $prog = $progs[$progKode] ?? null;
            if ($jj && $prog) {
                foreach ($group1Tiers as $count => $harga) {
                    $this->upsertPrice($prog->id, $jj->id, $pkt->id, $count, $count, $harga);
                }
            }
        }

        // Group 2: SMAK & UMUM
        $group2Tiers = [1 => 350000, 2 => 300000, 3 => 260000, 4 => 220000, 5 => 200000];
        foreach (['JENJANG_SMAK', 'JENJANG_UMUM'] as $jjKode) {
            $jj = $jjs[$jjKode] ?? null;
            $progKode = str_replace('JENJANG_', 'PROGRAM_', $jjKode) . '_CODING';
            $prog = $progs[$progKode] ?? null;
            if ($jj && $prog) {
                foreach ($group2Tiers as $count => $harga) {
                    $this->upsertPrice($prog->id, $jj->id, $pkt->id, $count, $count, $harga);
                }
            }
        }
    }

    protected function upsertPrice($programId, $jenjangId, $paketId, $min, $max, $harga)
    {
        PaketHarga::withTrashed()->updateOrCreate(
            [
                'program_id' => $programId,
                'jenjang_id' => $jenjangId,
                'paket_id' => $paketId,
                'min_siswa' => $min,
                'max_siswa' => $max,
                'effective_from' => $this->effectiveFrom,
            ],
            [
                'harga' => $harga,
                'effective_to' => null,
                'status' => 'Aktif',
                'deleted_at' => null,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Modules\Student\Domain\Models\Murid;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Academic\Domain\Models\Kelas;
use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Catalog\Domain\Models\Program;
use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Catalog\Domain\Models\PaketHarga;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealisticBalineseSeeder extends Seeder
{
    public function run(): void
    {
        $balineseNames = [
            ['nama' => 'I Putu Gede Saputra', 'jk' => 'L'],
            ['nama' => 'Ni Wayan Kadek Lestari', 'jk' => 'P'],
            ['nama' => 'I Made Arya Wijaya', 'jk' => 'L'],
            ['nama' => 'Ni Made Indah Permata', 'jk' => 'P'],
            ['nama' => 'I Nyoman Tri Budiarta', 'jk' => 'L'],
            ['nama' => 'Ni Nyoman Siska Amanda', 'jk' => 'P'],
            ['nama' => 'I Ketut Agus Setiawan', 'jk' => 'L'],
            ['nama' => 'Ni Ketut Ayu Mahaswari', 'jk' => 'P'],
            ['nama' => 'Anak Agung Ngurah Gede', 'jk' => 'L'],
            ['nama' => 'Ida Ayu Putu Manik', 'jk' => 'P'],
            ['nama' => 'I Gusti Ngurah Bagus', 'jk' => 'L'],
            ['nama' => 'Ni Komang Sri Wahyuni', 'jk' => 'P'],
            ['nama' => 'Gede Bagus Swastika', 'jk' => 'L'],
            ['nama' => 'Kadek Dwi Ariyanti', 'jk' => 'P'],
            ['nama' => 'Komang Adi Wiguna', 'jk' => 'L'],
            ['nama' => 'Ketut Sari Rejeki', 'jk' => 'P'],
            ['nama' => 'I Putu Oka Darmawan', 'jk' => 'L'],
            ['nama' => 'Ni Wayan Desi Ratnasari', 'jk' => 'P'],
            ['nama' => 'I Made Budi Astawa', 'jk' => 'L'],
            ['nama' => 'Ni Kadek Yeni Lestari', 'jk' => 'P'],
        ];

        $addresses = [
            'Jl. Teuku Umar No. 10, Denpasar',
            'Perum Dalung Permai Blok C, Badung',
            'Jl. Raya Ubud No. 45, Gianyar',
            'Perumahan Taman Griya, Jimbaran',
            'Jl. Bypass Ngurah Rai No. 100, Sanur',
            'Jl. Gatot Subroto Barat, Denpasar',
            'Jl. Raya Kapal, Mengwi',
            'Perumahan Canggu Permai, Badung',
            'Jl. Cargo Permai, Denpasar',
            'Jl. Raya Sesetan No. 88, Denpasar',
        ];

        $schools = [
            'SDN 1 Denpasar',
            'SDN 3 Dalung',
            'SMPN 1 Denpasar',
            'SMPN 2 Kuta',
            'SMAN 1 Denpasar',
            'SMAN 4 Denpasar',
            'SD CHIS Bali',
            'Bali Island School',
        ];

        $jenjangs = Jenjang::all();
        $programs = Program::all();
        $pakets = Paket::all();

        if ($jenjangs->isEmpty() || $programs->isEmpty() || $pakets->isEmpty()) {
            $this->command->error('Pastikan Jenjang, Program, dan Paket sudah di-seed terlebih dahulu!');
            return;
        }

        // 0. CLEAR EVERYTHING EXCEPT CORE CATALOG
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('kelas_enrollment')->truncate();
        DB::table('sesi_absensi_murid')->truncate();
        DB::table('sesi_logbook_murid')->truncate();
        DB::table('sesi_logbook')->truncate();
        DB::table('realisasi_jadwal_kerja')->truncate();
        DB::table('jadwal_kerja')->truncate();
        DB::table('kelas')->truncate();
        DB::table('enrollments')->truncate();
        DB::table('murid')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::transaction(function () use ($balineseNames, $addresses, $schools, $jenjangs, $programs, $pakets) {
            foreach ($balineseNames as $index => $data) {
                $jenjang = $jenjangs->random();

                // 1. Create Murid
                $murid = Murid::updateOrCreate(
                    ['kode_murid' => 'M' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'nama_lengkap' => $data['nama'],
                        'jenis_kelamin' => $data['jk'],
                        'tanggal_lahir' => Carbon::now()->subYears(rand(7, 17))->subDays(rand(1, 365)),
                        'no_hp' => '081' . rand(100000000, 999999999),
                        'email' => strtolower(str_replace(' ', '.', $data['nama'])) . '@gmail.com',
                        'alamat' => $addresses[array_rand($addresses)],
                        'jenjang_id' => $jenjang->id,
                        'sekolah_asal' => $schools[array_rand($schools)],
                        'kelas_sekolah' => rand(1, 6),
                        'nama_wali' => ($data['jk'] == 'L' ? 'I ' : 'Ni ') . 'Wayan Orang Tua',
                        'no_hp_wali' => '081' . rand(100000000, 999999999),
                        'hubungan_wali' => 'Orang Tua',
                        'status' => 'Aktif',
                    ]
                );

                // 2. Create Enrollment ONLY
                $numEnrollments = rand(1, 2);
                for ($i = 0; $i < $numEnrollments; $i++) {
                    $filteredPrograms = $programs->filter(function ($p) use ($jenjang) {
                        return str_contains($p->nama, $jenjang->nama);
                    });
                    $program = $filteredPrograms->isEmpty() ? $programs->random() : $filteredPrograms->random();
                    $paket = $pakets->random();

                    $paketHarga = PaketHarga::where([
                        'program_id' => $program->id,
                        'jenjang_id' => $jenjang->id,
                        'paket_id' => $paket->id,
                    ])->first();

                    $hargaFinal = $paketHarga ? $paketHarga->harga : 500000;

                    $enrollment = Enrollment::updateOrCreate(
                        [
                            'murid_id' => $murid->id,
                            'program_id' => $program->id,
                            'jenjang_id' => $jenjang->id,
                        ],
                        [
                            'kode_enrollment' => 'E' . Carbon::now()->format('ymd') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                            'paket_id' => $paket->id,
                            'jumlah_siswa' => 1,
                            'harga_final' => $hargaFinal,
                            'tanggal_mulai' => Carbon::now()->startOfMonth(),
                            'status' => 'Aktif',
                            'biaya_pendaftaran_amount' => 200000,
                            'biaya_pendaftaran_status' => rand(0, 1) ? 'PAID' : 'UNPAID',
                            'biaya_pendaftaran_due_date' => Carbon::now()->addDays(7),
                        ]
                    );
                }
            }
        });

        $this->command->info('Seed Berhasil: 20 Murid & Pendaftaran dibuat. Kelas & Jadwal kosong.');
    }
}

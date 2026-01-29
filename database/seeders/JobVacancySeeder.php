<?php

namespace Database\Seeders;

use App\Modules\HR\Domain\Models\JobVacancy;
use Illuminate\Database\Seeder;

class JobVacancySeeder extends Seeder
{
    /**
     * Seed job vacancies aligned with landing-shineedu detail (deskripsi, kualifikasi, tanggung jawab, benefit).
     */
    public function run(): void
    {
        $vacancies = [
            [
                'title' => 'Guru Bahasa Inggris',
                'location' => 'Denpasar, Bali',
                'employment_type' => 'Full-time',
                'description' => 'Kami mencari guru Bahasa Inggris yang bersemangat dan berdedikasi untuk bergabung dengan tim kami. Anda akan bertanggung jawab untuk mengajar siswa dari berbagai tingkat usia dengan metode yang menyenangkan dan efektif.',
                'posted_at' => now(),
                'end_at' => now()->addMonth(),
                'requirements' => [
                    'Sarjana Pendidikan Bahasa Inggris atau setara',
                    'Pengalaman mengajar minimal 1 tahun',
                    'Sertifikasi TEFL/TESOL merupakan nilai tambah',
                    'Memiliki kemampuan komunikasi yang baik',
                    'Kreatif dan inovatif dalam mengajar',
                    'Sabar dalam menghadapi anak-anak',
                ],
                'responsibilities' => [
                    'Menyiapkan dan menyampaikan materi pembelajaran Bahasa Inggris',
                    'Melakukan evaluasi kemajuan siswa secara berkala',
                    'Membuat laporan perkembangan siswa',
                    'Berpartisipasi dalam kegiatan sekolah',
                    'Mengikuti pelatihan pengembangan profesional',
                ],
                'benefits' => [
                    'Gaji kompetitif',
                    'Tunjangan kesehatan',
                    'Pelatihan berkelanjutan',
                    'Lingkungan kerja yang nyaman',
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Staff Administrasi',
                'location' => 'Kuta, Bali',
                'employment_type' => 'Full-time',
                'description' => 'Kami mencari staff administrasi yang terorganisir dan efisien untuk mengelola operasional harian di kantor kami.',
                'posted_at' => now(),
                'end_at' => now()->addMonth(),
                'requirements' => ['Minimal lulusan D3/S1', 'Menguasai Microsoft Office', 'Teliti dan mampu bekerja dalam tim'],
                'responsibilities' => ['Mengelola pendaftaran siswa baru', 'Menjaga dokumentasi', 'Menangani komunikasi dengan orang tua siswa'],
                'benefits' => ['Gaji kompetitif', 'Tunjangan kesehatan', 'Jam kerja reguler'],
                'is_active' => true,
            ],
            [
                'title' => 'Guru Matematika',
                'location' => 'Denpasar, Bali',
                'employment_type' => 'Part-time',
                'description' => 'Bergabunglah dengan tim kami sebagai guru matematika paruh waktu.',
                'posted_at' => now(),
                'end_at' => now()->addMonth(),
                'requirements' => ['Sarjana Matematika atau Pendidikan Matematika', 'Pengalaman mengajar minimal 1 tahun'],
                'responsibilities' => ['Mengajar matematika untuk siswa SD hingga SMA', 'Menyiapkan materi dan evaluasi'],
                'benefits' => ['Gaji per jam kompetitif', 'Jadwal fleksibel'],
                'is_active' => true,
            ],
            ['title' => 'Marketing Executive', 'location' => 'Kuta', 'employment_type' => 'Full-time', 'is_active' => true],
            ['title' => 'IT Support', 'location' => 'Denpasar', 'employment_type' => 'Full-time', 'is_active' => true],
            ['title' => 'Cleaning Service', 'location' => 'Kuta', 'employment_type' => 'Full-time', 'is_active' => true],
        ];

        foreach ($vacancies as $v) {
            $title = $v['title'];
            unset($v['title']);
            JobVacancy::updateOrCreate(
                ['title' => $title],
                $v
            );
        }
    }
}

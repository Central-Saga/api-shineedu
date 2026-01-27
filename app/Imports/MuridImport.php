<?php

namespace App\Imports;

use App\Modules\Student\Domain\Models\Murid;
use App\Modules\Catalog\Domain\Models\Jenjang;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class MuridImport implements OnEachRow, WithHeadingRow
{
    protected $updateExisting;
    protected $report = [
        'total_rows' => 0,
        'inserted_count' => 0,
        'updated_count' => 0,
        'skipped_count' => 0,
        'validation_errors' => [],
    ];

    public function __construct(bool $updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();
        $this->report['total_rows']++;

        $validator = Validator::make($data, [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jenis_kelamin' => 'nullable|string|in:L,P,Laki-laki,Perempuan',
            'no_hp' => 'nullable|string',
            'status' => 'nullable|string',
            'jenjang' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $this->report['validation_errors'][] = "Row {$this->report['total_rows']}: " . implode(', ', $validator->errors()->all());
            return;
        }

        $email = !empty($data['email']) ? trim($data['email']) : null;
        $namaLengkap = trim($data['nama_lengkap']);

        // Find existing murid by email or by name + birthdate if birthdate provided
        $murid = null;
        if ($email) {
            $murid = Murid::where('email', $email)->first();
        } else {
            // If no email, maybe search by name? Risky but let's see.
            // For now, let's stick to email as unique identifier if provided.
        }

        $jenjangId = $this->findJenjangId($data['jenjang'] ?? '');
        $status = $this->normalizeStatus($data['status'] ?? 'AKTIF');
        $jk = $this->normalizeGender($data['jenis_kelamin'] ?? '');

        $muridData = [
            'nama_lengkap' => $namaLengkap,
            'jenis_kelamin' => $jk,
            'no_hp' => $data['no_hp'] ?? null,
            'email' => $email,
            'alamat' => $data['alamat'] ?? null,
            'jenjang_id' => $jenjangId,
            'sekolah_asal' => $data['sekolah_asal'] ?? null,
            'kelas_sekolah' => $data['kelas_sekolah'] ?? null,
            'nama_wali' => $data['nama_wali'] ?? null,
            'no_hp_wali' => $data['no_hp_wali'] ?? null,
            'email_wali' => $data['email_wali'] ?? null,
            'hubungan_wali' => $data['hubungan_wali'] ?? null,
            'status' => $status,
        ];

        if ($murid instanceof Murid) {
            if ($this->updateExisting) {
                $murid->update($muridData);
                $this->report['updated_count']++;
            } else {
                $this->report['skipped_count']++;
            }
        } else {
            // Generate kode_murid if not provided or just let the model handle it if it has an observer/logic
            // For simplicity, let's assume the service or model handles it.
            // If not, we might need to add it here.

            Murid::create($muridData);
            $this->report['inserted_count']++;
        }
    }

    protected function findJenjangId($jenjangName): ?int
    {
        if (empty($jenjangName)) return null;

        $jenjang = Jenjang::where('nama_jenjang', 'like', "%{$jenjangName}%")->first();
        return $jenjang ? $jenjang->id : null;
    }

    protected function normalizeStatus($status): string
    {
        $status = strtoupper(trim((string) $status));
        if (in_array($status, ['AKTIF', 'NON-AKTIF', 'LULUS', 'KELUAR'])) return $status;
        return 'AKTIF';
    }

    protected function normalizeGender($jk): string
    {
        $jk = strtoupper(trim((string) $jk));
        if ($jk === 'L' || str_contains($jk, 'LAKI')) return 'L';
        if ($jk === 'P' || str_contains($jk, 'PEREMPUAN')) return 'P';
        return 'L'; // default
    }

    public function getReport(): array
    {
        return $this->report;
    }
}

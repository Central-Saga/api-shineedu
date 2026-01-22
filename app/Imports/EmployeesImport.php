<?php

namespace App\Imports;

use App\Modules\HR\Domain\Models\Employee;
use App\Modules\Identity\Domain\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $user = User::where('email', $row['user_email'])->first();

        // Parse date properly
        $tanggalLahir = null;
        if (!empty($row['tanggal_lahir'])) {
            try {
                $tanggalLahir = Carbon::parse($row['tanggal_lahir']);
            } catch (\Exception $e) {
                // Ignore or handle
            }
        }

        return new Employee([
            'kode_karyawan'     => $row['kode_karyawan'],
            'user_id'           => $user ? $user->id : null,
            'kategori_karyawan' => $row['kategori_karyawan'] ?? 'Kontrak',
            'subtipe_kontrak'   => $row['subtipe_kontrak'] ?? null,
            'tipe_gaji'         => $row['tipe_gaji'] ?? 'Bulanan',
            'gaji_pokok'        => $row['gaji_pokok'] ?? 0,
            'bank_nama'         => $row['bank_nama'] ?? null,
            'bank_no_rekening'  => $row['bank_no_rekening'] ?? null,
            'nomor_hp'          => $row['nomor_hp'] ?? null,
            'alamat'            => $row['alamat'] ?? null,
            'tanggal_lahir'     => $tanggalLahir,
            'status'            => $row['status'] ?? 'aktif',
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_karyawan'     => 'required|unique:karyawan,kode_karyawan',
            'user_email'        => 'required|exists:users,email',
            'kategori_karyawan' => 'required|string',
            'tipe_gaji'         => 'required|string',
            'gaji_pokok'        => 'numeric|min:0',
        ];
    }
}

<?php

namespace App\Exports;

use App\Modules\Student\Domain\Models\Murid;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MuridExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Murid::query()->with('jenjang');

        if ($keyword = $this->request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', "%{$keyword}%")
                    ->orWhere('kode_murid', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        if ($jenjangId = $this->request->get('jenjang_id')) {
            if ($jenjangId !== '__all__') {
                $query->where('jenjang_id', $jenjangId);
            }
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        $sortWhitelist = ['nama_lengkap', 'kode_murid', 'status', 'created_at', 'updated_at'];
        if (!in_array($sort, $sortWhitelist)) {
            $sort = 'created_at';
        }

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID Murid',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'No HP',
            'Email',
            'Alamat',
            'Jenjang',
            'Sekolah Asal',
            'Kelas Sekolah',
            'Nama Wali',
            'No HP Wali',
            'Email Wali',
            'Hubungan Wali',
            'Status',
            'Created At',
            'Updated At',
        ];
    }

    public function map($murid): array
    {
        return [
            $murid->kode_murid,
            $murid->nama_lengkap,
            $murid->jenis_kelamin,
            $murid->tanggal_lahir ? $murid->tanggal_lahir->format('Y-m-d') : '-',
            $murid->no_hp,
            $murid->email,
            $murid->alamat,
            $murid->jenjang?->nama ?? '-',
            $murid->sekolah_asal,
            $murid->kelas_sekolah,
            $murid->nama_wali,
            $murid->no_hp_wali,
            $murid->email_wali,
            $murid->hubungan_wali,
            $murid->status,
            $murid->created_at ? $murid->created_at->format('Y-m-d H:i:s') : '-',
            $murid->updated_at ? $murid->updated_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}

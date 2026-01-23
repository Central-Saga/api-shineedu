<?php

namespace App\Exports;

use App\Modules\HR\Domain\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AbsensiExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Absensi::query()->with(['karyawan.user']);

        if ($keyword = $this->request->get('q')) {
            $query->whereHas('karyawan.user', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            })->orWhereHas('karyawan', function ($q) use ($keyword) {
                $q->where('kode_karyawan', 'like', "%{$keyword}%");
            });
        }

        if ($status = $this->request->get('status_kehadiran')) {
            if ($status !== '__all__') {
                $query->where('status_kehadiran', $status);
            }
        }

        if ($startDate = $this->request->get('start_date')) {
            $query->where('tanggal', '>=', $startDate);
        }

        if ($endDate = $this->request->get('end_date')) {
            $query->where('tanggal', '<=', $endDate);
        }

        $sort = $this->request->get('sort_by', 'tanggal');
        $dir = $this->request->get('sort_dir', 'desc');

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Karyawan',
            'Nama Karyawan',
            'Tanggal',
            'Status Kehadiran',
            'Jam Masuk',
            'Jam Pulang',
            'Durasi (Menit)',
            'Sumber Absen',
            'Catatan',
            'Created At',
        ];
    }

    public function map($absensi): array
    {
        return [
            $absensi->id,
            $absensi->karyawan?->kode_karyawan ?? '-',
            $absensi->karyawan?->user?->name ?? '-',
            $absensi->tanggal,
            $absensi->status_kehadiran,
            $absensi->jam_masuk,
            $absensi->jam_pulang,
            $absensi->durasi,
            $absensi->sumber_absen,
            $absensi->catatan,
            $absensi->created_at ? $absensi->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}

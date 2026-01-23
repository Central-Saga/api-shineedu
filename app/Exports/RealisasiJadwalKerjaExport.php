<?php

namespace App\Exports;

use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RealisasiJadwalKerjaExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = RealisasiJadwalKerja::query()->with(['jadwalKerja.guru.user', 'approver', 'guruPengajar.user', 'guruPengganti.user']);

        if ($keyword = $this->request->get('q')) {
            $query->whereHas('jadwalKerja', function ($q) use ($keyword) {
                $q->where('mata_pelajaran', 'like', "%{$keyword}%")
                    ->orWhereHas('guru', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
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
            'Tanggal',
            'Jadwal (MP)',
            'Guru (Jadwal)',
            'Status',
            'Disetujui Oleh',
            'Sumber',
            'Catatan',
            'Ruangan Kelas',
            'Guru Pengajar',
            'Guru Pengganti',
            'Created At',
        ];
    }

    public function map($realisasi): array
    {
        return [
            $realisasi->id,
            $realisasi->tanggal,
            $realisasi->jadwalKerja?->mata_pelajaran ?? '-',
            $realisasi->jadwalKerja?->guru?->user?->name ?? '-',
            $realisasi->status,
            $realisasi->approver?->name ?? '-',
            $realisasi->sumber,
            $realisasi->catatan,
            $realisasi->ruangan_kelas,
            $realisasi->guruPengajar?->user?->name ?? '-',
            $realisasi->guruPengganti?->user?->name ?? '-',
            $realisasi->created_at ? $realisasi->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}

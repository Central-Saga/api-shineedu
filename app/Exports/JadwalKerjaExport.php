<?php

namespace App\Exports;

use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JadwalKerjaExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = JadwalKerja::query()->with(['guru.user']);

        if ($keyword = $this->request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('mata_pelajaran', 'like', "%{$keyword}%")
                    ->orWhere('kategori', 'like', "%{$keyword}%")
                    ->orWhereHas('guru.user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($hari = $this->request->get('hari')) {
            if ($hari !== '__all__') {
                $query->where('hari', $hari);
            }
        }

        if ($kategori = $this->request->get('kategori')) {
            if ($kategori !== '__all__') {
                $query->where('kategori', $kategori);
            }
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        $sort = $this->request->get('sort_by', 'hari');
        $dir = $this->request->get('sort_dir', 'asc');

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kategori',
            'Mata Pelajaran',
            'Hari',
            'Nomor Sesi',
            'Jam Mulai',
            'Jam Selesai',
            'Tarif',
            'Status',
            'Ruangan Kelas',
            'Guru Pengajar',
            'Created At',
        ];
    }

    public function map($jadwal): array
    {
        return [
            $jadwal->id,
            $jadwal->kategori,
            $jadwal->mata_pelajaran,
            $jadwal->hari,
            $jadwal->nomor_sesi,
            $jadwal->jam_mulai,
            $jadwal->jam_selesai,
            $jadwal->tarif,
            $jadwal->status,
            $jadwal->ruangan_kelas,
            $jadwal->guru?->user?->name ?? '-',
            $jadwal->created_at ? $jadwal->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}

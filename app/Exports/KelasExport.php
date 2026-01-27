<?php

namespace App\Exports;

use App\Modules\Academic\Domain\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KelasExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Kelas::query()->with(['program', 'jenjang'])->withCount('schedules as jumlah_sesi');

        if ($keyword = $this->request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_kelas', 'like', "%{$keyword}%")
                    ->orWhere('kode_kelas', 'like', "%{$keyword}%");
            });
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        if ($programId = $this->request->get('program_id')) {
            if ($programId !== '__all__') {
                $query->where('program_id', $programId);
            }
        }

        if ($jenjangId = $this->request->get('jenjang_id')) {
            if ($jenjangId !== '__all__') {
                $query->where('jenjang_id', $jenjangId);
            }
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        $sortWhitelist = ['nama_kelas', 'kode_kelas', 'status', 'created_at', 'updated_at'];
        if (!in_array($sort, $sortWhitelist)) {
            $sort = 'created_at';
        }

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID Kelas',
            'Nama Kelas',
            'Program',
            'Jenjang',
            'Tipe Kelas',
            'Private',
            'Kapasitas',
            'Status',
            'Periode Mulai',
            'Periode Selesai',
            'Ruangan Default',
            'Created At',
            'Updated At',
        ];
    }

    public function map($kelas): array
    {
        return [
            $kelas->kode_kelas,
            $kelas->nama_kelas,
            $kelas->program?->nama ?? '-',
            $kelas->jenjang?->nama ?? '-',
            $kelas->tipe_kelas,
            $kelas->mode_private ? 'Ya' : 'Tidak',
            $kelas->kapasitas,
            $kelas->status,
            $kelas->periode_mulai ? $kelas->periode_mulai->format('Y-m-d') : '-',
            $kelas->periode_selesai ? $kelas->periode_selesai->format('Y-m-d') : '-',
            $kelas->ruangan_default,
            $kelas->created_at ? $kelas->created_at->format('Y-m-d H:i:s') : '-',
            $kelas->updated_at ? $kelas->updated_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}

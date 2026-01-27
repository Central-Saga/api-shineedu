<?php

namespace App\Exports;

use App\Modules\AcademicSessions\Domain\Models\Session;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SessionExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;
    protected $kelasId;

    public function __construct($request, $kelasId = null)
    {
        $this->request = $request;
        $this->kelasId = $kelasId;
    }

    public function query()
    {
        $query = Session::query()->with(['kelas', 'guruPengajar', 'guruPengganti']);

        if ($this->kelasId) {
            $query->where('kelas_id', $this->kelasId);
        }

        if ($from = $this->request->get('from')) {
            $query->where('tanggal', '>=', $from);
        }

        if ($to = $this->request->get('to')) {
            $query->where('tanggal', '<=', $to);
        }

        if ($status = $this->request->get('status_sesi')) {
            $query->where('status_sesi', $status);
        }

        return $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kelas',
            'Tanggal',
            'Jam',
            'Guru',
            'Guru Pengganti',
            'Status Sesi',
            'Status Guru',
            'Ruangan',
            'Catatan',
        ];
    }

    public function map($sesi): array
    {
        return [
            $sesi->id,
            $sesi->kelas?->nama_kelas ?? '-',
            $sesi->tanggal ? $sesi->tanggal->format('d/m/Y') : '-',
            $sesi->jam_mulai_aktual . ' - ' . $sesi->jam_selesai_aktual,
            $sesi->guruPengajar?->user?->name ?? '-',
            $sesi->guruPengganti?->user?->name ?? '-',
            $sesi->status_sesi,
            $sesi->status_kehadiran_guru,
            $sesi->ruangan_kelas ?? '-',
            $sesi->catatan ?? '-',
        ];
    }
}

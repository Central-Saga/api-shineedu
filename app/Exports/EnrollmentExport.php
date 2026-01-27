<?php

namespace App\Exports;

use App\Modules\Enrollment\Domain\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EnrollmentExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Enrollment::query()->with(['murid', 'program', 'jenjang', 'paket', 'kelas']);

        if ($keyword = $this->request->get('q')) {
            $query->whereHas('murid', function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%");
            });
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Murid',
            'Program',
            'Jenjang',
            'Paket',
            'Kelas',
            'Status',
            'Registration Date',
        ];
    }

    public function map($enrollment): array
    {
        return [
            $enrollment->id,
            $enrollment->murid?->nama ?? '-',
            $enrollment->program?->nama ?? '-',
            $enrollment->jenjang?->nama ?? '-',
            $enrollment->paket?->nama ?? '-',
            $enrollment->kelas?->nama ?? '-',
            $enrollment->status,
            $enrollment->created_at ? $enrollment->created_at->format('Y-m-d') : '-',
        ];
    }
}

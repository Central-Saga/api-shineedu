<?php

namespace App\Exports;

use App\Modules\HR\Domain\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Payroll::query()
            ->with(['employee.user']);

        if ($this->request->has('bulan') && $this->request->has('tahun')) {
            $query->where('bulan', $this->request->bulan)
                ->where('tahun', $this->request->tahun);
        }

        if ($q = $this->request->get('q')) {
            $query->whereHas('employee', function ($sub) use ($q) {
                $sub->where('kode_karyawan', 'like', "%{$q}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        if ($status = $this->request->get('status')) {
            $query->where('status', $status);
        }

        return $query->orderBy('karyawan_id', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Karyawan',
            'Nama Karyawan',
            'Periode',
            'Gaji Pokok',
            'Fee Mengajar',
            'Potongan',
            'Gaji Bersih',
            'Status',
            'Tgl Pembayaran',
        ];
    }

    public function map($payroll): array
    {
        return [
            $payroll->id,
            $payroll->employee->kode_karyawan ?? '-',
            $payroll->employee->user->name ?? '-',
            $payroll->bulan . '/' . $payroll->tahun,
            $payroll->gaji_pokok,
            $payroll->total_fee_mengajar,
            $payroll->total_potongan,
            $payroll->gaji_bersih,
            $payroll->status,
            $payroll->tanggal_pembayaran ? $payroll->tanggal_pembayaran->format('Y-m-d') : '-',
        ];
    }
}

<?php

namespace App\Exports;

use App\Modules\Finance\Domain\Models\KasTransaksi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KasTransaksiExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = KasTransaksi::query()
            ->with(['createdBy', 'shift']);

        $filters = $this->request->only([
            'q',
            'type',
            'kategori',
            'tanggal_from',
            'tanggal_to',
            'reference_type',
        ]);

        if (isset($filters['q']) && $filters['q']) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('keterangan', 'like', "%{$q}%")
                    ->orWhere('pihak', 'like', "%{$q}%")
                    ->orWhere('receipt_number', 'like', "%{$q}%");
            });
        }

        if (isset($filters['type']) && $filters['type'] && $filters['type'] !== '__all__') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['kategori']) && $filters['kategori'] && $filters['kategori'] !== '__all__') {
            $query->where('kategori', $filters['kategori']);
        }

        if (isset($filters['tanggal_from']) && $filters['tanggal_from']) {
            $query->where('tanggal', '>=', $filters['tanggal_from']);
        }

        if (isset($filters['tanggal_to']) && $filters['tanggal_to']) {
            $query->where('tanggal', '<=', $filters['tanggal_to']);
        }

        if (isset($filters['reference_type']) && $filters['reference_type']) {
            $query->where('reference_type', $filters['reference_type']);
        }

        return $query->orderBy('tanggal', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'No Kwitansi',
            'Tanggal',
            'Tipe',
            'Kategori',
            'Jumlah (IDR)',
            'Metode Pembayaran',
            'Keterangan',
            'Pihak',
            'Dibuat Oleh',
            'Shift ID',
            'Created At',
        ];
    }

    public function map($transaksi): array
    {
        return [
            $transaksi->id,
            $transaksi->receipt_number,
            $transaksi->tanggal ? $transaksi->tanggal->format('Y-m-d') : '-',
            $transaksi->type,
            $transaksi->kategori,
            $transaksi->amount,
            $transaksi->metode,
            $transaksi->keterangan,
            $transaksi->pihak,
            $transaksi->createdBy->name ?? '-',
            $transaksi->shift_id ?? '-',
            $transaksi->created_at ? $transaksi->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}

@extends('exports.layout')

@section('title', 'Laporan Transaksi Kas')
@section('document_title', 'LAPORAN TRANSAKSI KAS')

@section('content')
@if(!empty($filters['tanggal_from']) || !empty($filters['tanggal_to']) || !empty($filters['kategori']) ||
!empty($filters['type']))
<div style="background: #f9fafb; padding: 12px; margin-bottom: 20px; border-left: 3px solid #C8102E; font-size: 9pt;">
    <strong style="color: #C8102E;">Filter yang Diterapkan:</strong>
    <div style="margin-top: 5px;">
        @if(!empty($filters['tanggal_from']))
        <p style="margin: 3px 0;">Dari Tanggal: <strong>{{
                \Carbon\Carbon::parse($filters['tanggal_from'])->format('d/m/Y') }}</strong></p>
        @endif
        @if(!empty($filters['tanggal_to']))
        <p style="margin: 3px 0;">Sampai Tanggal: <strong>{{
                \Carbon\Carbon::parse($filters['tanggal_to'])->format('d/m/Y') }}</strong></p>
        @endif
        @if(!empty($filters['kategori']))
        <p style="margin: 3px 0;">Kategori: <strong>{{ $filters['kategori'] }}</strong></p>
        @endif
        @if(!empty($filters['type']))
        <p style="margin: 3px 0;">Tipe: <strong>{{ $filters['type'] === 'IN' ? 'Kas Masuk' : 'Kas Keluar' }}</strong>
        </p>
        @endif
    </div>
</div>
@endif

<table>
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 10%;">Tanggal</th>
            <th style="width: 8%;">Tipe</th>
            <th style="width: 15%;">Jumlah</th>
            <th style="width: 10%;">Metode</th>
            <th style="width: 12%;">Kategori</th>
            <th style="width: 15%;">Pihak</th>
            <th style="width: 25%;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @php
        $totalIn = 0;
        $totalOut = 0;
        @endphp
        @forelse($transactions as $index => $trx)
        @php
        if ($trx->type === 'IN') {
        $totalIn += $trx->amount;
        } else {
        $totalOut += $trx->amount;
        }
        @endphp
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $trx->tanggal->format('d/m/Y') }}</td>
            <td>
                <span class="status-badge" style="background-color: {{ $trx->type === 'IN' ? '#dcfce7' : '#fee2e2' }};
                           color: {{ $trx->type === 'IN' ? '#166534' : '#991b1b' }};">
                    {{ $trx->type === 'IN' ? 'IN' : 'OUT' }}
                </span>
            </td>
            <td style="text-align: right; font-weight: bold; color: {{ $trx->type === 'IN' ? '#166534' : '#991b1b' }};">
                {{ $trx->type === 'IN' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
            </td>
            <td>{{ $trx->metode }}</td>
            <td>{{ $trx->kategori }}</td>
            <td>{{ $trx->pihak ?? '-' }}</td>
            <td>{{ $trx->keterangan ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align: center; color: #9ca3af; font-style: italic;">
                Tidak ada data transaksi
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if(count($transactions) > 0)
<div style="background: #f9fafb; padding: 15px; border: 2px solid #C8102E; border-radius: 4px; margin-top: 20px;">
    <h3 style="margin: 0 0 10px 0; color: #C8102E; font-size: 12pt;">Ringkasan</h3>
    <table style="width: 100%; border: none; margin: 0;">
        <tr style="background: none;">
            <td style="border: none; padding: 5px 0; font-size: 10pt;">
                <strong>Total Kas Masuk:</strong>
            </td>
            <td
                style="border: none; padding: 5px 0; text-align: right; font-weight: bold; color: #166534; font-size: 11pt;">
                +Rp {{ number_format($totalIn, 0, ',', '.') }}
            </td>
        </tr>
        <tr style="background: none;">
            <td style="border: none; padding: 5px 0; font-size: 10pt;">
                <strong>Total Kas Keluar:</strong>
            </td>
            <td
                style="border: none; padding: 5px 0; text-align: right; font-weight: bold; color: #991b1b; font-size: 11pt;">
                -Rp {{ number_format($totalOut, 0, ',', '.') }}
            </td>
        </tr>
        <tr style="background: none; border-top: 2px solid #C8102E;">
            <td style="border: none; padding: 10px 0 5px 0; font-size: 11pt;">
                <strong>Saldo:</strong>
            </td>
            <td
                style="border: none; padding: 10px 0 5px 0; text-align: right; font-weight: bold; font-size: 13pt; color: #111827;">
                Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}
            </td>
        </tr>
    </table>
</div>
@endif
@endsection

@extends('exports.layout')

@section('title', 'Laporan Penggajian')
@section('document_title', 'LAPORAN PENGGAJIAN')

@section('content')
<div style="margin-bottom: 20px;">
    <strong>Periode:</strong> {{ request('bulan') }}/{{ request('tahun') }}
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Karyawan</th>
            <th>Nama Karyawan</th>
            <th>Gaji Pokok</th>
            <th>Fee Mengajar</th>
            <th>Potongan</th>
            <th>Gaji Bersih</th>
            <th>Status</th>
            <th>Tgl Bayar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payrolls as $index => $p)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $p->employee->kode_karyawan ?? '-' }}</td>
            <td>{{ $p->employee->user->name ?? '-' }}</td>
            <td align="right">{{ number_format($p->gaji_pokok, 0, ',', '.') }}</td>
            <td align="right">{{ number_format($p->total_fee_mengajar, 0, ',', '.') }}</td>
            <td align="right">{{ number_format($p->total_potongan, 0, ',', '.') }}</td>
            <td align="right">{{ number_format($p->gaji_bersih, 0, ',', '.') }}</td>
            <td>{{ $p->status }}</td>
            <td>{{ $p->tanggal_pembayaran ? $p->tanggal_pembayaran->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
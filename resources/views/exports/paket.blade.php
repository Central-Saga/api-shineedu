@extends('exports.layout')

@section('title', 'Laporan Data Paket')
@section('document_title', 'LAPORAN DATA PAKET')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Tipe</th>
            <th>Pertemuan/Bulan</th>
            <th>Durasi (Menit)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->kode }}</td>
            <td>{{ $item->nama }}</td>
            <td>{{ $item->tipe }}</td>
            <td>{{ $item->pertemuan_per_bulan }}</td>
            <td>{{ $item->durasi_menit }}</td>
            <td>
                <span class="status-badge" style="background-color: {{ strtolower($item->status) === 'aktif' ? '#dcfce7' : '#fee2e2' }};
                           color: {{ strtolower($item->status) === 'aktif' ? '#166534' : '#991b1b' }};">
                    {{ $item->status }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

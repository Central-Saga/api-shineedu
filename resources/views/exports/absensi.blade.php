@extends('exports.layout')

@section('title', 'Laporan Data Absensi')
@section('document_title', 'LAPORAN DATA ABSENSI')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Karyawan</th>
            <th>Tanggal</th>
            <th>Status Kehadiran</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Durasi (Menit)</th>
            <th>Sumber Absen</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->karyawan && $item->karyawan->user ? $item->karyawan->user->name : '-' }}</td>
            <td>{{ $item->tanggal }}</td>
            <td>{{ ucfirst($item->status_kehadiran instanceof \BackedEnum ? $item->status_kehadiran->value :
                $item->status_kehadiran) }}</td>
            <td>{{ $item->getRawOriginal('jam_masuk') ?: '-' }}</td>
            <td>{{ $item->getRawOriginal('jam_keluar') ?: '-' }}</td>
            <td>{{ $item->durasi_menit ?: 0 }}</td>
            <td>{{ ucfirst($item->sumber_absen instanceof \BackedEnum ? $item->sumber_absen->value :
                $item->sumber_absen) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

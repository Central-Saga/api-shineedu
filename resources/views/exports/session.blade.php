@extends('exports.layout')

@section('title', 'Laporan Data Sesi Akademik')

@section('content')
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Kelas</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Guru</th>
            <th>Guru Pengganti</th>
            <th>Status Sesi</th>
            <th>Status Guru</th>
            <th>Ruangan</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->kelas?->nama_kelas ?? '-' }}</td>
            <td>{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}</td>
            <td>{{ $item->jam_mulai_aktual }} - {{ $item->jam_selesai_aktual }}</td>
            <td>{{ $item->guruPengajar?->user?->name ?? '-' }}</td>
            <td>{{ $item->guruPengganti?->user?->name ?? '-' }}</td>
            <td>{{ $item->status_sesi }}</td>
            <td>{{ $item->status_kehadiran_guru }}</td>
            <td>{{ $item->ruangan_kelas ?? '-' }}</td>
            <td>{{ $item->catatan ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

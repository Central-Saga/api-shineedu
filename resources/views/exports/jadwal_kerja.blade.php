@extends('exports.layout')

@section('title', 'Laporan Jadwal Kerja')
@section('document_title', 'LAPORAN JADWAL KERJA')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Mata Pelajaran</th>
            <th>Kategori</th>
            <th>Hari</th>
            <th>Jam</th>
            <th>Guru Pengajar</th>
            <th>Ruangan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->mata_pelajaran }}</td>
            <td>{{ ucfirst($item->kategori) }}</td>
            <td>{{ $item->hari }}</td>
            <td>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
            <td>{{ $item->guru && $item->guru->user ? $item->guru->user->name : '-' }}</td>
            <td>{{ $item->ruangan_kelas }}</td>
            <td>{{ ucfirst($item->status instanceof \BackedEnum ? $item->status->value : $item->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

@extends('exports.layout')

@section('title', 'Laporan Realisasi Jadwal Kerja')
@section('document_title', 'LAPORAN REALISASI JADWAL KERJA')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Mata Pelajaran</th>
            <th>Guru Pengajar</th>
            <th>Guru Pengganti</th>
            <th>Jam</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->tanggal }}</td>
            <td>{{ $item->jadwalKerja ? $item->jadwalKerja->mata_pelajaran : '-' }}</td>
            <td>{{ $item->guruPengajar && $item->guruPengajar->user ? $item->guruPengajar->user->name : '-' }}</td>
            <td>{{ $item->guruPengganti && $item->guruPengganti->user ? $item->guruPengganti->user->name : '-' }}</td>
            <td>{{ $item->jadwalKerja ? $item->jadwalKerja->jam_mulai : '-' }} - {{
                $item->jadwalKerja ? $item->jadwalKerja->jam_selesai : '-' }}</td>
            <td>{{ ucfirst($item->status instanceof \BackedEnum ? $item->status->value : $item->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

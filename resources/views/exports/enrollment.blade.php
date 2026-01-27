@extends('exports.layout')

@section('title', 'Laporan Data Pendaftaran')
@section('document_title', 'LAPORAN DATA PENDAFTARAN')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Murid</th>
            <th>Program</th>
            <th>Jenjang</th>
            <th>Paket</th>
            <th>Kelas</th>
            <th>Status</th>
            <th>Tgl Daftar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->murid?->nama ?? '-' }}</td>
            <td>{{ $item->program?->nama ?? '-' }}</td>
            <td>{{ $item->jenjang?->nama ?? '-' }}</td>
            <td>{{ $item->paket?->nama ?? '-' }}</td>
            <td>{{ $item->kelas?->nama ?? '-' }}</td>
            <td>{{ $item->status }}</td>
            <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

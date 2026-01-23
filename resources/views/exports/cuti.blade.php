@extends('exports.layout')

@section('title', 'Laporan Data Cuti')
@section('document_title', 'LAPORAN DATA CUTI')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Karyawan</th>
            <th>Jenis</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Total Hari</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->karyawan && $item->karyawan->user ? $item->karyawan->user->name : '-' }}</td>
            <td>{{ ucfirst($item->jenis) }}</td>
            <td>{{ $item->start_date }}</td>
            <td>{{ $item->end_date }}</td>
            <td>{{ $item->total_hari }}</td>
            <td>{{ ucfirst($item->status instanceof \BackedEnum ? $item->status->value : $item->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

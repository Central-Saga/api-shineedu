@extends('exports.layout')

@section('title', 'Laporan Data Murid')
@section('document_title', 'LAPORAN DATA MURID')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>No. HP</th>
            <th>Jenjang</th>
            <th>Sekolah</th>
            <th>Status</th>
            <th>Tgl Lahir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($murids as $index => $murid)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $murid->kode_murid }}</td>
            <td>{{ $murid->nama_lengkap }}</td>
            <td>{{ $murid->email }}</td>
            <td>{{ $murid->no_hp }}</td>
            <td>{{ $murid->jenjang?->nama ?? '-' }}</td>
            <td>{{ $murid->sekolah_asal ?? $murid->sekolah ?? '-' }}</td>
            <td>
                <span class="status-badge" style="background-color: {{ strtolower($murid->status) === 'aktif' ? '#dcfce7' : '#fee2e2' }};
                           color: {{ strtolower($murid->status) === 'aktif' ? '#166534' : '#991b1b' }};">
                    {{ $murid->status }}
                </span>
            </td>
            <td>{{ $murid->tanggal_lahir ? $murid->tanggal_lahir->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

@extends('exports.layout')

@section('title', 'Laporan Data Kelas')
@section('document_title', 'LAPORAN DATA KELAS')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Kelas</th>
            <th>Program</th>
            <th>Jenjang</th>
            <th>Tipe</th>
            <th>Sesi</th>
            <th>Status</th>
            <th>Periode</th>
        </tr>
    </thead>
    <tbody>
        @foreach($kelas as $index => $k)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $k->kode_kelas }}</td>
            <td>{{ $k->nama_kelas }}</td>
            <td>{{ $k->program?->nama ?? '-' }}</td>
            <td>{{ $k->jenjang?->nama ?? '-' }}</td>
            <td>{{ $k->tipe_kelas }}</td>
            <td>{{ $k->jumlah_sesi }}</td>
            <td>
                <span class="status-badge" style="background-color: {{ strtolower($k->status) === 'aktif' ? '#dcfce7' : '#fee2e2' }};
                           color: {{ strtolower($k->status) === 'aktif' ? '#166534' : '#991b1b' }};">
                    {{ $k->status }}
                </span>
            </td>
            <td>
                {{ $k->periode_mulai ? $k->periode_mulai->format('d/m/Y') : '-' }} -
                {{ $k->periode_selesai ? $k->periode_selesai->format('d/m/Y') : '-' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

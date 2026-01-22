@extends('exports.layout')

@section('title', 'Laporan Data Karyawan')
@section('document_title', 'LAPORAN DATA KARYAWAN')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Nama</th>
            <th>Kategori</th>
            <th>Tipe Gaji</th>
            <th>Gaji Pokok</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $index => $employee)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $employee->kode_karyawan }}</td>
            <td>{{ $employee->user ? $employee->user->name : '-' }}</td>
            <td>{{ $employee->kategori_karyawan }}</td>
            <td>{{ $employee->tipe_gaji }}</td>
            <td>Rp {{ number_format($employee->gaji_pokok, 0, ',', '.') }}</td>
            <td>
                <span class="status-badge"
                    style="background-color: {{ $employee->status === 'Aktif' ? '#dcfce7' : '#fee2e2' }}; color: {{ $employee->status === 'Aktif' ? '#166534' : '#991b1b' }};">
                    {{ $employee->status }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

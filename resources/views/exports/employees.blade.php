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
            <td>Rp {{ number_format((float)($employee->gaji_pokok ?? 0), 0, ',', '.') }}</td>
            <td>
                <span class="status-badge"
                    style="background-color: {{ strtolower($employee->status instanceof \BackedEnum ? $employee->status->value : $employee->status) === 'aktif' ? '#dcfce7' : '#fee2e2' }}; color: {{ strtolower($employee->status instanceof \BackedEnum ? $employee->status->value : $employee->status) === 'aktif' ? '#166534' : '#991b1b' }};">
                    {{ $employee->status instanceof \App\Modules\HR\Domain\Enums\EmployeeStatus ?
                    $employee->status->label() : $employee->status }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

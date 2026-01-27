@extends('exports.layout')

@section('title', 'Laporan Harga Paket')
@section('document_title', 'LAPORAN HARGA PAKET')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Program</th>
            <th>Jenjang</th>
            <th>Paket</th>
            <th>Siswa (Min-Max)</th>
            <th>Harga</th>
            <th>Effective From</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ data_get($item, 'program.nama', '-') }}</td>
            <td>{{ data_get($item, 'jenjang.nama', '-') }}</td>
            <td>{{ data_get($item, 'paket.nama', '-') }}</td>
            <td>{{ $item->min_siswa }} - {{ $item->max_siswa }}</td>
            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
            <td>{{ $item->effective_from ? $item->effective_from->format('d/m/Y') : '-' }}</td>
            <td>
                <span class="status-badge" style="background-color: {{ strtolower($item->status) === 'aktif' ? '#dcfce7' : '#fee2e2' }};
                           color: {{ strtolower($item->status) === 'aktif' ? '#166534' : '#991b1b' }};">
                    {{ $item->status }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

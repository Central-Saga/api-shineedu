@extends('exports.layout')

@section('title', 'Laporan Data Jenjang')
@section('document_title', 'LAPORAN DATA JENJANG')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Status</th>
            <th>Tgl Dibuat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->kode }}</td>
            <td>{{ $item->nama }}</td>
            <td>
                <span class="status-badge" style="background-color: {{ strtolower($item->status) === 'aktif' ? '#dcfce7' : '#fee2e2' }};
                           color: {{ strtolower($item->status) === 'aktif' ? '#166534' : '#991b1b' }};">
                    {{ $item->status }}
                </span>
            </td>
            <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

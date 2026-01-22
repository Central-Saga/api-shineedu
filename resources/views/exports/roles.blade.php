@extends('exports.layout')

@section('title', 'Laporan Data Role & Hak Akses')
@section('document_title', 'LAPORAN DATA ROLE & HAK AKSES')

@section('content')
<table>
    <thead>
        <tr>
            <th width="50">No</th>
            <th width="150">Nama Role</th>
            <th>Hak Akses (Permissions)</th>
            <th width="150">Tgl Dibuat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($roles as $index => $role)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td style="font-weight: bold; color: #C8102E;">{{ $role->name }}</td>
            <td style="font-size: 8pt; color: #4b5563;">
                {{ $role->permissions->pluck('name')->join(', ') }}
            </td>
            <td>{{ $role->created_at ? $role->created_at->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

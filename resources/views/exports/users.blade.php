@extends('exports.layout')

@section('title', 'Laporan Data User')
@section('document_title', 'LAPORAN DATA USER')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Status</th>
            <th>Role</th>
            <th>Tgl Terdaftar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $index => $user)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                @php
                $statusValue = $user->status instanceof \BackedEnum ? $user->status->value : ($user->status instanceof
                \UnitEnum ? $user->status->name : $user->status);
                @endphp
                <span class="status-badge"
                    style="background-color: {{ $statusValue === 'Aktif' ? '#dcfce7' : '#fee2e2' }}; color: {{ $statusValue === 'Aktif' ? '#166534' : '#991b1b' }};">
                    {{ $statusValue }}
                </span>
            </td>
            <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

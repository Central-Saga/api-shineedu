@extends('exports.layout')

@section('title', 'Laporan Lamaran Kerja')
@section('document_title', 'LAPORAN LAMARAN KERJA')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tracking Code</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Posisi</th>
            <th>Pendidikan</th>
            <th>Pengalaman</th>
            <th>Status</th>
            <th>Tgl Lamar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($applications as $index => $app)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $app->tracking_code }}</td>
            <td>{{ $app->first_name }} {{ $app->last_name }}</td>
            <td>{{ $app->email }}</td>
            <td>{{ $app->phone }}</td>
            <td>{{ $app->jobVacancy->title ?? '-' }}</td>
            <td>{{ $app->education }}</td>
            <td>{{ $app->experience }}</td>
            <td>{{ $app->status }}</td>
            <td>{{ $app->created_at ? $app->created_at->format('d/m/Y') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
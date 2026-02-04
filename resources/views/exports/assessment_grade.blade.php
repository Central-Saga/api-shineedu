@extends('exports.layout')

@section('title', 'Laporan Nilai Assessment')
@section('document_title', 'LAPORAN NILAI ASSESSMENT')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No Sertifikat</th>
            <th>Nama Murid</th>
            <th>Program</th>
            <th>Guru</th>
            <th>Template</th>
            <th>Total Score</th>
            <th>Avg Score</th>
            <th>Predicate</th>
            <th>Level</th>
            <th>Generated At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grades as $index => $grade)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $grade->certificate_no }}</td>
            <td>{{ $grade->enrollment->student->nama_lengkap ?? '-' }}</td>
            <td>{{ $grade->enrollment->program->nama ?? '-' }}</td>
            <td>{{ $grade->teacher->user->name ?? '-' }}</td>
            <td>{{ $grade->certificateTemplate->name ?? '-' }}</td>
            <td>{{ $grade->total_score }}</td>
            <td>{{ $grade->average_score }}</td>
            <td>{{ $grade->predicate }}</td>
            <td>{{ $grade->certificate_level }}</td>
            <td>{{ $grade->generated_at ? $grade->generated_at->format('d/m/Y H:i') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
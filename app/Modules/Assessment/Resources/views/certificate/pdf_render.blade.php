<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            /* Or custom font */
            -webkit-print-color-adjust: exact;
        }

        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
            page-break-after: always;
            overflow: hidden;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            object-fit: cover;
        }

        .content-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
        }

        .absolute-text {
            position: absolute;
            /* transform: translate(-50%, -50%); Centering logic if needed, usually just top/left */
        }
    </style>
</head>

<body>

    <!-- Page 1: Cover -->
    <div class="page">
        <img src="{{ $coverUrl }}" class="bg-image">
        <div class="content-layer">
            <!-- Dynamic Content based on Mapping -->
            @if(isset($mapping['cover']))
            @foreach($mapping['cover'] as $field => $coords)
            <div class="absolute-text"
                style="top: {{ $coords['top'] ?? '0' }}; left: {{ $coords['left'] ?? '0' }}; font-size: {{ $coords['fontSize'] ?? '12pt' }}; color: {{ $coords['color'] ?? '#000' }}; font-weight: {{ $coords['fontWeight'] ?? 'normal' }}; width: {{ $coords['width'] ?? 'auto' }}; text-align: {{ $coords['textAlign'] ?? 'left' }};">

                @if($field === 'student_name')
                {{ $student->nama_lengkap }}
                @elseif($field === 'program_name')
                {{ $enrollment->program->nama ?? 'Program' }}
                @elseif($field === 'date')
                {{ $grade->generated_at ? $grade->generated_at->format('d F Y') : date('d F Y') }}
                @elseif($field === 'certificate_no')
                {{ $grade->certificate_no }}
                @else
                {{ $field }}
                @endif

            </div>
            @endforeach
            @endif
        </div>
    </div>

    <!-- Page 2: Result -->
    @if($resultUrl)
    <div class="page">
        <img src="{{ $resultUrl }}" class="bg-image">
        <div class="content-layer">
            <!-- Scores and Details -->
            @if(isset($mapping['result']))
            @foreach($mapping['result'] as $field => $coords)
            <div class="absolute-text"
                style="top: {{ $coords['top'] ?? '0' }}; left: {{ $coords['left'] ?? '0' }}; font-size: {{ $coords['fontSize'] ?? '12pt' }}; color: {{ $coords['color'] ?? '#000' }}; font-weight: {{ $coords['fontWeight'] ?? 'normal' }}; width: {{ $coords['width'] ?? 'auto' }}; text-align: {{ $coords['textAlign'] ?? 'left' }};">

                @if(str_starts_with($field, 'score_'))
                @php $key = str_replace('score_', '', $field); @endphp
                {{ $grade->scores[$key] ?? '-' }}
                @elseif($field === 'average_score')
                {{ $grade->average_score }}
                @elseif($field === 'total_score')
                {{ $grade->total_score }}
                @elseif($field === 'predicate')
                {{ $grade->predicate }}
                @elseif($field === 'level')
                {{ $grade->certificate_level }}
                @endif

            </div>
            @endforeach
            @endif
        </div>
    </div>
    @endif

</body>

</html>

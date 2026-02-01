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

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: 'Arial', sans-serif;
            -webkit-print-color-adjust: exact;
            background-color: #f0f0f0;
        }

        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
            page-break-after: always;
            overflow: hidden;
            background-color: #fff;
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
            z-index: 1;
            object-fit: fill;
            /* Use fill to ensure it covers the exact A4 bounds */
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
            z-index: 100;
            display: block;
            /* background: rgba(255,0,0,0.1); Debugging background */
        }
    </style>
</head>

@php
$parseStyle = function($coords) {
$styles = [];
$mapping = [
'top' => 'top',
'left' => 'left',
'width' => 'width',
'height' => 'height',
'fontSize' => 'font-size',
'color' => 'color',
'fontWeight' => 'font-weight',
'textAlign' => 'text-align',
'backgroundColor' => 'background-color',
'padding' => 'padding',
'border' => 'border',
'borderRadius' => 'border-radius',
'letterSpacing' => 'letter-spacing',
];

foreach($mapping as $key => $cssProp) {
if (isset($coords[$key]) && $coords[$key] !== null) {
$value = $coords[$key];
// Append units if numeric
if (is_numeric($value)) {
if ($key === 'fontSize') {
$value .= 'pt';
} else if (in_array($key, ['top', 'left', 'width', 'height', 'padding', 'borderRadius', 'letterSpacing'])) {
$value .= 'px';
}
}
$styles[] = "$cssProp: $value";
}
}
return implode('; ', $styles);
};
@endphp

<body>

    <!-- Page 1: Cover -->
    <div class="page">
        @if($coverUrl)
        <img src="{{ $coverUrl }}" class="bg-image">
        @endif

        <div class="content-layer">
            @php
            // Detect if user has custom text mapped.
            $hasCustomText = false;
            if(isset($mapping['cover']) && !empty($mapping['cover'])) {
            foreach($mapping['cover'] as $coords) {
            if(isset($coords['text']) && !empty($coords['text'])) {
            $hasCustomText = true;
            break;
            }
            }
            }
            @endphp

            @if($hasCustomText)
            {{-- Render User's Custom Mapping (Absolute Positioning) --}}
            @foreach($mapping['cover'] as $field => $coords)
            <div class="absolute-text" style="{{ $parseStyle($coords) }}">
                @if(isset($coords['text']))
                {{ $coords['text'] }}
                @elseif($field === 'student_name')
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
            @else
            {{-- Render Official Shine Education Layout (Default) --}}
            <div style="text-align: center; margin-top: 80px; width: 85%; margin-left: auto; margin-right: auto;">
                {{-- Logo --}}
                @if($logoUrl)
                <img src="{{ $logoUrl }}" style="width: 100px; height: auto; margin-bottom: 10px;">
                @else
                <div style="width: 100px; height: 100px; background: red; margin: 0 auto 10px;">Logo</div>
                @endif

                <div style="font-size: 14pt; margin-bottom: 5px;">LEMBAGA KURSUS DAN PELATIHAN (LKP)</div>
                <div style="font-size: 28pt; font-weight: bold; color: red; margin-bottom: 5px;">SHINE EDUCATION BALI
                </div>
                <div style="font-size: 11pt; margin-bottom: 60px;">NPSN: K997966/IJIN OPERASIONAL:
                    421.9/0019/DPMPTSP/2022</div>

                <div style="font-size: 14pt; margin-bottom: 10px;">Di Berikan Kepada:</div>

                <div style="font-size: 24pt; font-weight: bold; text-transform: uppercase; margin-bottom: 20px;">
                    {{ $student->nama_lengkap }}
                </div>

                <div style="font-size: 14pt; width: 80%; margin: 0 auto; line-height: 1.5;">
                    Telah menyelesaikan Kursus Aplikasi Komputer program<br>
                    <span style="font-weight: bold;">{{ $enrollment->program->nama ?? 'Program' }}</span>, dinyatakan
                    <span style="font-weight: bold;">LULUS</span>
                </div>

                {{-- Footer/Signature Section --}}
                <div style="position: absolute; bottom: 130px; right: 130px; text-align: center;">
                    <div style="margin-bottom: 80px;">
                        Tabanan, {{ $grade->generated_at ? $grade->generated_at->format('d F Y') : date('d F Y') }}<br>
                        Pimpinan LKP Shine Education Bali
                    </div>

                    <div style="font-weight: bold; font-size: 12pt; text-decoration: underline;">Ni Putu Sri Indrawati,
                        S.Pd</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <style>
        /* ... existing styles ... */
        .default-table-container {
            width: 80%;
            margin: 0 auto;
            position: absolute;
            top: 150px;
            left: 10%;
            z-index: 50;
        }

        .result-title {
            text-align: center;
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .grade-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .grade-table th {
            background-color: #fca510;
            /* Yellowish orange from screenshot */
            color: black;
            padding: 12px;
            font-size: 14pt;
            text-transform: uppercase;
            border: 1px solid #e5e7eb;
        }

        .grade-table td {
            padding: 12px;
            font-size: 12pt;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .grade-table td.subject-name {
            text-align: left;
            padding-left: 20px;
        }

        .signature-section {
            position: absolute;
            bottom: 150px;
            right: 100px;
            text-align: center;
            width: 250px;
        }

        .signature-line {
            border-bottom: 2px solid black;
            margin-top: 80px;
            margin-bottom: 10px;
        }

        .director-text {
            font-size: 11pt;
            font-weight: bold;
        }

        .director-name {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>

    <!-- Page 2: Result -->
    @php
    $hasScoreMapping = false;
    if(isset($mapping['result'])) {
    foreach($mapping['result'] as $field => $coords) {
    if (str_starts_with($field, 'score_')) {
    $hasScoreMapping = true;
    break;
    }
    }
    }
    @endphp

    @if($resultUrl || !$hasScoreMapping)
    <div class="page">
        @if($resultUrl)
        <img src="{{ $resultUrl }}" class="bg-image">
        @endif

        <div class="content-layer">
            @if(isset($mapping['result']))
            @foreach($mapping['result'] as $field => $coords)
            <div class="absolute-text" style="{{ $parseStyle($coords) }}">
                @if(isset($coords['text']))
                {{ $coords['text'] }}
                @elseif(str_starts_with($field, 'score_'))
                @php $key = str_replace('score_', '', $field); @endphp
                {{ $grade->scores[$key] ?? '-' }}
                @elseif($field === 'average_score')
                {{ $grade->average_score }}
                @elseif($field === 'total_score')
                {{ $grade->total_score }}
                @elseif($field === 'student_name')
                {{ $student->nama_lengkap }}
                @elseif($field === 'certificate_no')
                {{ $grade->certificate_no }}
                @elseif($field === 'date')
                {{ $grade->generated_at ? $grade->generated_at->format('d F Y') : date('d F Y') }}
                @elseif($field === 'predicate')
                {{ $grade->predicate }}
                @elseif($field === 'level')
                {{ $grade->certificate_level }}
                @endif
            </div>
            @endforeach
            @endif

            {{-- Render Default Table if NO explicitly mapped scores --}}
            @if(!$hasScoreMapping && !empty($grade->scores))
            <div class="default-table-container">
                <div class="result-title">AKADEMIC RESULT</div>

                <table class="grade-table">
                    <thead>
                        <tr>
                            <th style="width: 40%">THE MATERIALS</th>
                            <th style="width: 20%">SCORE</th>
                            <th style="width: 20%">TOTAL</th>
                            <th style="width: 20%">AVERAGE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $scoreKeys = array_keys($grade->scores);
                        $firstKey = $scoreKeys[0] ?? null;
                        $rowCount = count($grade->scores);
                        @endphp

                        @foreach($grade->scores as $subject => $score)
                        <tr>
                            <td class="subject-name">{{ ucfirst($subject) }}</td>
                            <td>{{ $score }}</td>

                            @if($subject === $firstKey)
                            <td rowspan="{{ $rowCount }}" style="font-weight: bold; font-size: 16pt;">{{
                                $grade->total_score }}</td>
                            <td rowspan="{{ $rowCount }}" style="font-weight: bold; font-size: 16pt;">{{
                                $grade->average_score }}</td>
                            @endif
                        </tr>
                        @endforeach

                        <tr>
                            <td colspan="2" style="text-align: left; padding-left: 20px; font-weight: bold;">PREDICATE
                            </td>
                            <td colspan="2" style="font-weight: bold; font-size: 14pt;">{{ $grade->predicate }}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 15px; font-size: 10pt; text-align: center;">
                    Berdasarkan hasil akademik, siswa dinyatakan lulus dengan predikat<br>
                    90-100 = A (Sangat Baik), 80-89 = B (Baik), 70-79 = C (Cukup), 60-69 = D (Kurang)
                </div>

                {{-- Dynamic Signature (Flows after text) --}}
                <div style="margin-top: 50px; text-align: right;">
                    <div style="display: inline-block; text-align: center; width: 250px; margin-right: 50px;">
                        <div style="margin-bottom: 80px;">
                            <div>Tabanan, {{ $grade->generated_at ? $grade->generated_at->format('d F Y') : date('d F
                                Y') }}</div>
                            <div class="director-text">Teacher</div>
                        </div>

                        <div class="director-name">{{ $grade->teacher->user->name ?? '.........................' }}
                        </div>
                        <div style="border-top: 2px solid black; width: 100%; margin: 5px auto 0;"></div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

</body>

</html>

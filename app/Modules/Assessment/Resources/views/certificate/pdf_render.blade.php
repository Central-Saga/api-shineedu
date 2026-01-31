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
            @if(isset($mapping['cover']))
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
            @endif
        </div>
    </div>

    <!-- Page 2: Result -->
    @if($resultUrl)
    <div class="page">
        <img src="{{ $resultUrl }}" class="bg-image">
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
        </div>
    </div>
    @endif

</body>

</html>

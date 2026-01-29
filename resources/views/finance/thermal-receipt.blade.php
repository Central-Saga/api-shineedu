<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.3;
            width: 58mm;
            margin: 0 auto;
            padding: 3mm;
            background: white;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 2px dashed #000;
        }

        .header h1 {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 9px;
            margin: 1px 0;
            line-height: 1.2;
        }

        .section {
            margin-bottom: 8px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 10px;
        }

        .row-full {
            margin: 2px 0;
            font-size: 10px;
        }

        .label {
            font-weight: bold;
        }

        .value {
            text-align: right;
            max-width: 60%;
            word-wrap: break-word;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .divider-double {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }

        .total-section {
            background: #f5f5f5;
            padding: 6px 4px;
            margin: 8px -3mm;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .total {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .terbilang {
            font-size: 9px;
            font-style: italic;
            text-align: center;
            margin-top: 2px;
        }

        .info-box {
            background: #f9f9f9;
            padding: 4px;
            margin: 6px 0;
            border: 1px solid #ddd;
            font-size: 9px;
        }

        .signature {
            margin-top: 15px;
            text-align: center;
        }

        .signature p {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .signature-line {
            margin: 25px auto 5px;
            border-top: 1px solid #000;
            width: 120px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px dashed #000;
            font-size: 9px;
        }

        .footer p {
            margin: 2px 0;
        }

        .thank-you {
            font-weight: bold;
            margin-bottom: 3px;
        }

        @media print {
            body {
                width: 58mm;
                margin: 0;
                padding: 3mm;
            }

            @page {
                size: 58mm auto;
                margin: 0;
            }

            .total-section {
                background: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-box {
                background: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        {{-- Shine Education Logo --}}
        <div style="margin-bottom: 8px;">
            <img src="{{ asset('images/shine-logo.png') }}" alt="Shine Education"
                style="width: 80px; height: auto; display: block; margin: 0 auto;">
        </div>
        <h1>SHINE EDUCATION BALI</h1>
        <p>Jl. Bakisan, Denbantas, Kec. Tabanan</p>
        <p>Kabupaten Tabanan, Bali 82123</p>
        <p>Telp: 0812-3752-2400</p>
    </div>

    <div class="section">
        <div class="row">
            <span class="label">No. Kwitansi:</span>
            <span class="value">{{ $transaction->receipt_number ?? '#' . $transaction->id }}</span>
        </div>
        <div class="row">
            <span class="label">Tanggal:</span>
            <span class="value">{{ $transaction->tanggal->format('d/m/Y H:i') }}</span>
        </div>
        @if($transaction->external_ref)
        <div class="row">
            <span class="label">Ref:</span>
            <span class="value">{{ $transaction->external_ref }}</span>
        </div>
        @endif
    </div>

    <div class="divider-double"></div>

    @php
    $studentName = null;
    $programName = null;

    // Try to get student and program from paketTopup
    if ($transaction->paketTopup) {
    if ($transaction->paketTopup->enrollment) {
    $studentName = $transaction->paketTopup->enrollment->murid->nama ?? null;
    $programName = $transaction->paketTopup->enrollment->program->nama ?? null;
    }
    }

    // Fallback to pihak if no student found
    $displayName = $studentName ?: $transaction->pihak;
    @endphp

    @if($displayName || $programName)
    <div class="info-box">
        @if($displayName)
        <div class="row-full">
            <span class="label">{{ $studentName ? 'Siswa' : 'Pihak' }}:</span>
        </div>
        <div class="row-full" style="margin-left: 8px;">
            {{ $displayName }}
        </div>
        @endif

        @if($programName)
        <div class="row-full" style="margin-top: 3px;">
            <span class="label">Program:</span>
        </div>
        <div class="row-full" style="margin-left: 8px;">
            {{ $programName }}
        </div>
        @endif
    </div>
    @endif

    <div class="divider"></div>

    <div class="section">
        <div class="row">
            <span class="label">Kategori:</span>
            <span class="value">{{ $transaction->kategori }}</span>
        </div>
        <div class="row">
            <span class="label">Metode:</span>
            <span class="value">{{ $transaction->metode }}</span>
        </div>
    </div>

    @if($transaction->payment_details && isset($transaction->payment_details['items']))
    <div class="divider"></div>
    <div class="section">
        <div class="row-full">
            <span class="label">Rincian Pembayaran:</span>
        </div>
        @foreach($transaction->payment_details['items'] as $item)
        <div class="row" style="margin-top: 3px; font-size: 9px;">
            <span style="max-width: 55%;">
                {{ $item['description'] ?? '-' }}
                @if(isset($item['quantity']) && $item['quantity'] > 0)
                <span style="font-size: 8px; color: #666;">({{ $item['quantity'] }} {{ $item['unit'] ?? 'item'
                    }})</span>
                @endif
            </span>
            <span class="value">Rp {{ number_format($item['amount'] ?? 0, 0, ',', '.') }}</span>
        </div>
        @endforeach
        <div style="border-top: 1px dashed #000; margin: 4px 0;"></div>
    </div>
    @endif

    @if($transaction->keterangan)
    <div class="divider"></div>
    <div class="section">
        <div class="row-full">
            <span class="label">Keterangan:</span>
        </div>
        <div class="row-full" style="margin-top: 2px; font-size: 9px;">
            {{ $transaction->keterangan }}
        </div>
    </div>
    @endif

    <div class="total-section">
        <div class="row total">
            <span class="label">TOTAL:</span>
            <span class="value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
        </div>
        <div class="terbilang">
            {{ ucwords(\Illuminate\Support\Str::lower(\App\Helpers\Terbilang::convert($transaction->amount))) }}
            Rupiah
        </div>
    </div>

    <div class="signature">
        <p>Penerima,</p>
        <div class="signature-line"></div>
        <p class="signature-name">{{ $transaction->createdBy->name ?? 'Admin' }}</p>
    </div>

    <div class="footer">
        <p class="thank-you">Terima kasih atas pembayaran Anda</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            // Small delay to ensure styles are loaded
            setTimeout(function() {
                window.print();
            }, 250);
        };
    </script>
</body>

</html>

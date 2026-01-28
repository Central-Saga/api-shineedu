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
            font-size: 12px;
            line-height: 1.4;
            width: 58mm;
            padding: 5mm;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 10px;
            margin: 1px 0;
        }

        .section {
            margin-bottom: 10px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .label {
            font-weight: bold;
        }

        .value {
            text-align: right;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .signature {
            margin-top: 20px;
            text-align: center;
        }

        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 150px;
            margin-left: auto;
            margin-right: auto;
        }

        @media print {
            body {
                width: 58mm;
            }

            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>SHINE EDUCATION</h1>
        <p>Jl. Contoh No. 123, Bali</p>
        <p>Telp: (0361) 123456</p>
    </div>

    <div class="section">
        <div class="row">
            <span class="label">No. Kwitansi:</span>
            <span class="value">#{{ $transaction->id }}</span>
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

    <div class="divider"></div>

    <div class="section">
        @if($transaction->enrollment)
        <div class="row">
            <span class="label">Siswa:</span>
        </div>
        <div class="row">
            <span>{{ $transaction->enrollment->murid->nama ?? '-' }}</span>
        </div>
        <div class="row">
            <span class="label">Program:</span>
        </div>
        <div class="row">
            <span>{{ $transaction->enrollment->program->nama ?? '-' }}</span>
        </div>
        @endif
    </div>

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
        @if($transaction->keterangan)
        <div class="row">
            <span class="label">Keterangan:</span>
        </div>
        <div class="row">
            <span>{{ $transaction->keterangan }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="section">
        <div class="row total">
            <span class="label">TOTAL:</span>
            <span class="value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span style="font-size: 10px;">{{
                ucwords(\Illuminate\Support\Str::lower(\App\Helpers\Terbilang::convert($transaction->amount))) }}
                Rupiah</span>
        </div>
    </div>

    <div class="signature">
        <p>Penerima,</p>
        <div class="signature-line"></div>
        <p>{{ $transaction->createdBy->name ?? 'Admin' }}</p>
    </div>

    <div class="footer">
        <p>Terima kasih atas pembayaran Anda</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
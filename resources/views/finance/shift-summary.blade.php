<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Summary #{{ $shift->id }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 58mm;
            padding: 3mm;
            background: white;
            color: #000;
        }

        @media print {
            body {
                width: 58mm;
                padding: 2mm;
            }

            @page {
                size: 58mm auto;
                margin: 0;
            }
        }

        .header {
            text-align: center;
            padding-bottom: 8px;
            border-bottom: 2px dashed #000;
            margin-bottom: 8px;
        }

        .header img {
            width: 60px;
            height: auto;
            display: block;
            margin: 0 auto 5px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }

        .header h2 {
            font-size: 12px;
            font-weight: normal;
            margin: 3px 0;
        }

        .section {
            padding: 6px 0;
            border-bottom: 1px dashed #000;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 10px;
        }

        .row .label {
            flex: 1;
        }

        .row .value {
            text-align: right;
            font-weight: bold;
        }

        .total-section {
            padding: 8px 0;
            margin: 8px 0;
            border: 2px solid #000;
            background: #f5f5f5;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: bold;
        }

        .variance-positive {
            color: green;
        }

        .variance-negative {
            color: red;
        }

        .footer {
            text-align: center;
            padding-top: 8px;
            font-size: 9px;
            color: #666;
        }

        .divider {
            border-bottom: 2px dashed #000;
            margin: 8px 0;
        }

        .notes-box {
            background: #f8f8f8;
            padding: 6px;
            margin: 6px 0;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ asset('images/shine-logo.png') }}" alt="Shine Education">
        <h1>SHINE EDUCATION BALI</h1>
        <h2>LAPORAN TUTUP SHIFT</h2>
    </div>

    <div class="section">
        <div class="row">
            <span class="label">ID Shift:</span>
            <span class="value">#{{ $shift->id }}</span>
        </div>
        <div class="row">
            <span class="label">Kasir:</span>
            <span class="value">{{ $shift->openedByUser->name ?? '-' }}</span>
        </div>
        <div class="row">
            <span class="label">Dibuka:</span>
            <span class="value">{{ $shift->opened_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">Ditutup:</span>
            <span class="value">{{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : '-' }}</span>
        </div>
    </div>

    <div class="section">
        <div class="row">
            <span class="label">Saldo Awal:</span>
            <span class="value">Rp {{ number_format($shift->opening_balance, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="section">
        <div class="section-title">Transaksi Masuk</div>
        @php $totalIn = 0; @endphp
        @foreach($summary['by_method'] as $method => $amounts)
        @if($amounts['in'] > 0)
        <div class="row">
            <span class="label">{{ $method }}:</span>
            <span class="value">Rp {{ number_format($amounts['in'], 0, ',', '.') }}</span>
        </div>
        @php $totalIn += $amounts['in']; @endphp
        @endif
        @endforeach
        <div class="row" style="border-top: 1px dashed #000; padding-top: 3px; margin-top: 3px;">
            <span class="label">Total Masuk:</span>
            <span class="value" style="color: green;">+Rp {{ number_format($summary['totals']['total_in'], 0, ',', '.')
                }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Transaksi Keluar</div>
        @foreach($summary['by_method'] as $method => $amounts)
        @if($amounts['out'] > 0)
        <div class="row">
            <span class="label">{{ $method }}:</span>
            <span class="value">-Rp {{ number_format($amounts['out'], 0, ',', '.') }}</span>
        </div>
        @endif
        @endforeach
        <div class="row" style="border-top: 1px dashed #000; padding-top: 3px; margin-top: 3px;">
            <span class="label">Total Keluar:</span>
            <span class="value" style="color: red;">-Rp {{ number_format($summary['totals']['total_out'], 0, ',', '.')
                }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="total-section">
        <div class="total-row">
            <span>Transaksi:</span>
            <span>{{ $summary['totals']['transaction_count'] }} item</span>
        </div>
        <div class="total-row">
            <span>Saldo Akhir:</span>
            <span>Rp {{ number_format($shift->expected_cash ?? ($shift->opening_balance + $summary['totals']['total_in']
                - $summary['totals']['total_out']), 0, ',', '.') }}</span>
        </div>
        @if($shift->isClosed())
        <div class="total-row">
            <span>Uang Dihitung:</span>
            <span>Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Selisih:</span>
            <span class="{{ $shift->variance >= 0 ? 'variance-positive' : 'variance-negative' }}">
                {{ $shift->variance >= 0 ? '' : '-' }}Rp {{ number_format(abs($shift->variance), 0, ',', '.') }}
            </span>
        </div>
        @endif
    </div>

    @if($shift->notes)
    <div class="notes-box">
        <strong>Catatan:</strong><br>
        {{ $shift->notes }}
    </div>
    @endif

    <div class="divider"></div>

    <div class="section">
        <div class="section-title">Per Kategori</div>
        @foreach($summary['by_category'] as $category => $amounts)
        @if($amounts['in'] > 0)
        <div class="row">
            <span class="label">{{ $category }}:</span>
            <span class="value" style="color: green;">+Rp {{ number_format($amounts['in'], 0, ',', '.') }}</span>
        </div>
        @endif
        @if($amounts['out'] > 0)
        <div class="row">
            <span class="label">{{ $category }}:</span>
            <span class="value" style="color: red;">-Rp {{ number_format($amounts['out'], 0, ',', '.') }}</span>
        </div>
        @endif
        @endforeach
    </div>

    <div class="footer">
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>SHINE EDUCATION BALI</p>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>

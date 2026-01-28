<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Kas</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            color: #666;
        }

        .filters {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .filters p {
            margin: 3px 0;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background: #333;
            color: white;
        }

        th {
            padding: 8px 5px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
        }

        td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-in {
            background: #d4edda;
            color: #155724;
        }

        .badge-out {
            background: #f8d7da;
            color: #721c24;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 4px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 11pt;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 12pt;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }

        .footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .amount-in {
            color: #28a745;
            font-weight: bold;
        }

        .amount-out {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>SHINE EDUCATION BALI</h1>
        <h2>Laporan Transaksi Kas</h2>
    </div>

    @if(!empty($filters['tanggal_from']) || !empty($filters['tanggal_to']) || !empty($filters['kategori']) ||
    !empty($filters['type']))
    <div class="filters">
        <strong>Filter:</strong>
        @if(!empty($filters['tanggal_from']))
        <p>Dari Tanggal: {{ \Carbon\Carbon::parse($filters['tanggal_from'])->format('d/m/Y') }}</p>
        @endif
        @if(!empty($filters['tanggal_to']))
        <p>Sampai Tanggal: {{ \Carbon\Carbon::parse($filters['tanggal_to'])->format('d/m/Y') }}</p>
        @endif
        @if(!empty($filters['kategori']))
        <p>Kategori: {{ $filters['kategori'] }}</p>
        @endif
        @if(!empty($filters['type']))
        <p>Tipe: {{ $filters['type'] === 'IN' ? 'Kas Masuk' : 'Kas Keluar' }}</p>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 7%;">Tipe</th>
                <th style="width: 12%;">Jumlah</th>
                <th style="width: 10%;">Metode</th>
                <th style="width: 13%;">Kategori</th>
                <th style="width: 15%;">Pihak</th>
                <th style="width: 35%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
            $totalIn = 0;
            $totalOut = 0;
            @endphp
            @forelse($transactions as $trx)
            @php
            if ($trx->type === 'IN') {
            $totalIn += $trx->amount;
            } else {
            $totalOut += $trx->amount;
            }
            @endphp
            <tr>
                <td>{{ $trx->tanggal->format('d/m/Y') }}</td>
                <td>
                    <span class="badge {{ $trx->type === 'IN' ? 'badge-in' : 'badge-out' }}">
                        {{ $trx->type === 'IN' ? 'IN' : 'OUT' }}
                    </span>
                </td>
                <td class="text-right {{ $trx->type === 'IN' ? 'amount-in' : 'amount-out' }}">
                    {{ $trx->type === 'IN' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                </td>
                <td>{{ $trx->metode }}</td>
                <td>{{ $trx->kategori }}</td>
                <td>{{ $trx->pihak ?? '-' }}</td>
                <td>{{ $trx->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data transaksi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Total Kas Masuk:</span>
            <span class="amount-in">+Rp {{ number_format($totalIn, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Total Kas Keluar:</span>
            <span class="amount-out">-Rp {{ number_format($totalOut, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row total">
            <span>Saldo:</span>
            <span>Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>SHINE EDUCATION BALI</p>
    </div>
</body>

</html>

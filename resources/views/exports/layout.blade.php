<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #C8102E;
            padding-bottom: 20px;
            margin-bottom: 30px;
            position: relative;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-container {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
        }

        .logo-img {
            width: 70px;
            height: auto;
        }

        .info-section {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }

        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #C8102E;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-tagline {
            font-size: 9pt;
            color: #666;
            margin: 2px 0 0 0;
            font-style: italic;
        }

        .document-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-decoration: underline;
            color: #111827;
        }

        .meta-info {
            margin-bottom: 20px;
            font-size: 9pt;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            background-color: #fff1f2;
            border: 1px solid #C8102E;
            padding: 10px 8px;
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
            color: #C8102E;
        }

        td {
            border: 1px solid #fecaca;
            padding: 8px;
            font-size: 9pt;
            color: #374151;
        }

        tr:nth-child(even) {
            background-color: #fffafa;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 50px;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="info-section">
                <h1 class="company-name">Shine Education Bali</h1>
                <p class="company-tagline">Smart Learning, Bright Future</p>
                <p style="font-size: 8pt; color: #666; margin: 5px 0 0 0;">
                    Munggu, Kec. Mengwi, Kabupaten Badung, Bali &bull; info@shineedu.com &bull; +62 812-3456-7890
                </p>
            </div>
        </div>
    </div>

    <div class="document-title">
        @yield('document_title')
    </div>

    <div class="meta-info">
        Dicetak pada: {{ now()->format('d F Y H:i:s') }}
    </div>

    @yield('content')

    <div class="footer">
        &copy; {{ date('Y') }} Shine Education Bali. Seluruh hak cipta dilindungi undang-undang.
    </div>
</body>

</html>

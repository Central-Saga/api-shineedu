<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        /* Base */
        body {
            background-color: #f3f4f6;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        /* Container */
        .container {
            display: block;
            margin: 0 auto !important;
            max-width: 580px;
            padding: 10px;
            width: 580px;
        }

        /* Card */
        .content {
            box-sizing: border-box;
            display: block;
            margin: 0 auto;
            max-width: 580px;
            padding: 10px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        /* Header */
        .header {
            padding: 20px 0;
            text-align: center;
        }

        .header h2 {
            color: #d97706;
            /* Shine Gold/Amber */
            margin: 0;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: -0.5px;
        }

        /* Body */
        .body-content {
            padding: 20px;
        }

        h1 {
            color: #111827;
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            text-align: left;
        }

        p {
            color: #4b5563;
            font-size: 15px;
            font-weight: normal;
            margin: 0;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        /* Data Box */
        .data-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            font-family: Consolas, Monaco, 'Courier New', monospace;
            color: #374151;
            font-size: 13px;
            white-space: pre-wrap;
            /* Preserve formatting */
        }

        /* Footer */
        .footer {
            clear: both;
            margin-top: 10px;
            text-align: center;
            width: 100%;
        }

        .footer p,
        .footer a {
            color: #9ca3af;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>

<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="header">
                    <h2>Shine Education Bali</h2>
                </div>
                <div class="content">
                    <table role="presentation" class="main">
                        <tr>
                            <td class="body-content">
                                <h1>{{ $subjectText }}</h1>

                                <div class="data-box">
                                    {!! nl2br(e($content)) !!}
                                </div>

                                <p>Silakan login ke dashboard untuk detail lebih lanjut.</p>

                                <p style="margin-top: 30px; font-size: 13px; color: #6b7280;">
                                    Salam hangat,<br>
                                    <strong>Tim Shine Education</strong>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="footer">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="content-block">
                                <p>System Notification &bull; Shine Education Bali</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>

</html>

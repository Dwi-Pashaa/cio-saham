<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembagian Dividen Saham</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 30px 10px 40px 10px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 620px;
            border-spacing: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .btn-cta {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 13px 28px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            display: inline-block;
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 0;">
@php
    $companyName  = $setting->company_name ?? 'PT CIO Network Solution';
    $companyTelp  = $setting->telp ?? '6281234567890';
    $companyEmail = $setting->email ?? 'finance@cionetwork.id';

    $investor     = $transfer->investor ?? null;
    $investorName = $investor?->name ?? 'Pemegang Saham';
    $amount       = number_format($transfer->amount ?? 0, 0, ',', '.');
    $code         = $transfer->code ?: ('INV-TRF-' . $transfer->id);
    $period       = !empty($transfer->period) ? \Carbon\Carbon::parse($transfer->period)->translatedFormat('F Y') : now()->translatedFormat('F Y');
    $invoiceUrl   = method_exists($transfer, 'getInvoiceUrl') ? $transfer->getInvoiceUrl() : null;
@endphp

<div class="wrapper">
    <table class="main" align="center">
        <!-- 1. Header Banner -->
        <tr>
            <td style="background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%); padding: 30px 28px; text-align: center;">
                <table width="100%">
                    <tr>
                        <td align="center">
                            @if(!empty($setting->logo))
                                <img src="{{ asset('storage/' . $setting->logo) }}" alt="{{ $companyName }}" width="56" style="margin-bottom: 12px; border-radius: 8px;">
                            @endif
                            <div style="display: inline-block; padding: 4px 12px; background: rgba(34, 197, 94, 0.2); border-radius: 20px; color: #4ade80; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 10px;">
                                BERHASIL DITRANSFER
                            </div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.3;">
                                Pembagian Dividen Saham
                            </h1>
                            <p style="margin: 6px 0 0 0; color: rgba(255, 255, 255, 0.85); font-size: 13px;">
                                Periode: {{ $period }} • {{ $companyName }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 2. Body Content -->
        <tr>
            <td style="padding: 28px 28px 20px 28px;">
                <p style="margin: 0 0 18px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                    Kepada Yth. <strong>{{ $investorName }}</strong>,<br>
                    Kami informasikan bahwa dana pembagian keuntungan dividen saham Anda untuk periode <strong>{{ $period }}</strong> telah berhasil diproses dan dikirimkan ke rekening terdaftar Anda.
                </p>

                <!-- Highlight Amount -->
                <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 18px; text-align: center; margin-bottom: 22px;">
                    <div style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">
                        Total Dividen Diterima
                    </div>
                    <div style="font-size: 26px; font-weight: 800; color: #065f46; font-family: monospace;">
                        Rp {{ $amount }}
                    </div>
                    <div style="font-size: 11px; color: #059669; margin-top: 4px;">
                        No. Invoice: <span style="font-family: monospace; font-weight: 700;">{{ $code }}</span>
                    </div>
                </div>

                @if($invoiceUrl)
                    <div style="text-align: center; margin-bottom: 24px;">
                        <a href="{{ $invoiceUrl }}" target="_blank" style="display: inline-block; padding: 10px 20px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; color: #1e293b; text-decoration: none; font-weight: 600; font-size: 13px;">
                            📄 Buka Invoice / Kwitansi Resmi di Browser ↗
                        </a>
                    </div>
                @endif

                <div style="text-align: center; margin: 24px 0 10px 0;">
                    <a href="{{ $dashboardUrl }}" class="btn-cta">
                        Buka Dashboard Investor Portal
                    </a>
                </div>
            </td>
        </tr>

        <!-- 3. Footer -->
        <tr>
            <td style="background-color: #f8fafc; padding: 22px 28px; border-top: 1px solid #e2e8f0; text-align: center;">
                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 700; color: #334155;">
                    {{ $companyName }}
                </p>
                <p style="margin: 0 0 10px 0; font-size: 11px; color: #64748b; line-height: 1.5;">
                    WhatsApp: {{ $companyTelp }} • Email: {{ $companyEmail }}
                </p>
                <p style="margin: 0; font-size: 10px; color: #94a3b8;">
                    &copy; {{ date('Y') }} {{ $companyName }}. Hak cipta dilindungi.
                </p>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Finansial Harian</title>
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
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            display: inline-block;
            letter-spacing: 0.02em;
        }
        @media only screen and (max-width: 600px) {
            .content-padding {
                padding: 20px 16px !important;
            }
            .header-padding {
                padding: 24px 16px !important;
            }
            .stat-box {
                display: block !important;
                width: 100% !important;
                margin-bottom: 10px !important;
            }
        }
    </style>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 0;">
@php
    $companyName = $setting->company_name ?? 'CIO Network Solution';
    $companyTelp = $setting->telp ?? '6281234567890';
    $companyEmail = $setting->email ?? 'support@cionetworksolution.com';
    
    $inflow = (float) ($dailyData['total_inflow'] ?? 0);
    $outflow = (float) ($dailyData['total_outflow'] ?? 0);
    $netProfit = (float) ($dailyData['net_profit'] ?? ($inflow - $outflow));
    $marginPct = (float) ($dailyData['profit_margin_pct'] ?? ($inflow > 0 ? ($netProfit / $inflow) * 100 : 0));
    
    $reportDate = !empty($dailyData['date']) 
        ? \Carbon\Carbon::parse($dailyData['date'])->translatedFormat('d F Y') 
        : \Carbon\Carbon::now()->translatedFormat('d F Y');
    
    $shares = (int) ($shareholder->total_shares ?? 0);
    $percentage = (float) ($shareholder->total_percentage ?? 0);
@endphp

<div class="wrapper">
    <table class="main" align="center">
        <!-- 1. Header Banner -->
        <tr>
            <td style="background: linear-gradient(135deg, #0a2540 0%, #1e40af 100%); padding: 32px 30px; text-align: center;" class="header-padding">
                <table width="100%">
                    <tr>
                        <td align="center">
                            @if(!empty($setting->logo))
                                <img src="{{ asset('storage/' . $setting->logo) }}" alt="{{ $companyName }}" width="60" style="margin-bottom: 12px; border-radius: 8px;">
                            @endif
                            <div style="color: #93c5fd; font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px;">
                                {{ $companyName }} &bull; PORTAL INVESTOR
                            </div>
                            <h1 style="color: #ffffff; margin: 0 0 8px 0; font-size: 22px; font-weight: 800; letter-spacing: -0.02em;">
                                Laporan Finansial Harian
                            </h1>
                            <div style="display: inline-block; background: rgba(255, 255, 255, 0.15); color: #ffffff; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                📅 Rekapan Tanggal: {{ $reportDate }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 2. Greeting & Intro -->
        <tr>
            <td style="padding: 28px 30px 16px 30px;" class="content-padding">
                <p style="margin: 0 0 12px 0; font-size: 15px; line-height: 1.6; color: #1e293b;">
                    Kepada Yth. <strong>{{ $shareholder->name }}</strong>,
                </p>
                <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #64748b;">
                    Berikut adalah rincian lengkap arus uang masuk, uang keluar, dan keuntungan bersih dari seluruh unit web terintegrasi serta pentotalan konsolidasi harian <strong>{{ $companyName }}</strong> untuk tanggal <strong>{{ $reportDate }}</strong>.
                </p>
            </td>
        </tr>

        <!-- 3. Rincian Arus Transaksi per Unit Web & Gateway -->
        <tr>
            <td style="padding: 8px 30px 16px 30px;" class="content-padding">
                <div style="font-size: 13px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                    🌐 RINCIAN ARUS TRANSAKSI PER UNIT WEB
                </div>

                @if(!empty($dailyData['web_breakdown']))
                    @foreach($dailyData['web_breakdown'] as $web)
                        @php
                            $wInflow = (float) ($web['total_inflow'] ?? 0);
                            $wOutflow = (float) ($web['total_outflow'] ?? 0);
                            $wNet = (float) ($web['net_profit'] ?? ($wInflow - $wOutflow));
                            $inflowItems = $web['inflow_items'] ?? [];
                            $outflowItems = $web['outflow_items'] ?? [];
                        @endphp
                        <table width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 14px; overflow: hidden;">
                            <!-- Header Unit Web -->
                            <tr>
                                <td style="background-color: #f1f5f9; padding: 10px 16px; border-bottom: 1px solid #e2e8f0;">
                                    <table width="100%">
                                        <tr>
                                            <td>
                                                <strong style="font-size: 14px; color: #0f172a;">{{ $web['name'] }}</strong>
                                                <span style="display: inline-block; background-color: #e2e8f0; color: #475569; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; font-family: monospace; margin-left: 4px;">{{ $web['code'] }}</span>
                                            </td>
                                            <td align="right" style="font-size: 12px; color: #64748b;">
                                                <strong>{{ $web['log_count'] ?? 0 }}</strong> Transaksi
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Body: Rincian Pos Uang Masuk & Keluar -->
                            <tr>
                                <td style="padding: 12px 16px;">
                                    <!-- Pos Uang Masuk -->
                                    <div style="font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase; margin-bottom: 6px;">
                                        🟢 Uang Masuk (Income)
                                    </div>
                                    @if(!empty($inflowItems))
                                        <table width="100%" style="margin-bottom: 10px;">
                                            @foreach(array_slice($inflowItems, 0, 5) as $item)
                                                <tr>
                                                    <td style="font-size: 12.5px; color: #334155; padding: 3px 0;">
                                                        &bull; {{ $item['title'] }}
                                                    </td>
                                                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: #16a34a; font-family: monospace; padding: 3px 0; white-space: nowrap;">
                                                        +Rp {{ number_format((float)$item['amount'], 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if(count($inflowItems) > 5)
                                                <tr>
                                                    <td colspan="2" style="font-size: 11px; color: #94a3b8; font-style: italic; padding-top: 2px;">
                                                        + {{ count($inflowItems) - 5 }} transaksi masuk lainnya terlampir di dokumen PDF
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    @else
                                        <div style="font-size: 12px; color: #94a3b8; font-style: italic; margin-bottom: 10px;">
                                            Tidak ada uang masuk pada periode ini.
                                        </div>
                                    @endif

                                    <!-- Pos Uang Keluar -->
                                    <div style="font-size: 11px; font-weight: 700; color: #dc2626; text-transform: uppercase; margin-bottom: 6px; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                                        🔴 Uang Keluar / Keperluan (Outcome)
                                    </div>
                                    @if(!empty($outflowItems))
                                        <table width="100%" style="margin-bottom: 10px;">
                                            @foreach(array_slice($outflowItems, 0, 5) as $item)
                                                <tr>
                                                    <td style="font-size: 12.5px; color: #334155; padding: 3px 0;">
                                                        &bull; {{ $item['title'] }}
                                                    </td>
                                                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: #dc2626; font-family: monospace; padding: 3px 0; white-space: nowrap;">
                                                        -Rp {{ number_format((float)$item['amount'], 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if(count($outflowItems) > 5)
                                                <tr>
                                                    <td colspan="2" style="font-size: 11px; color: #94a3b8; font-style: italic; padding-top: 2px;">
                                                        + {{ count($outflowItems) - 5 }} transaksi keluar lainnya terlampir di dokumen PDF
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    @else
                                        <div style="font-size: 12px; color: #94a3b8; font-style: italic; margin-bottom: 10px;">
                                            Tidak ada pengeluaran pada periode ini.
                                        </div>
                                    @endif

                                    <!-- Subtotal Box per Web -->
                                    <table width="100%" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; margin-top: 4px;">
                                        <tr>
                                            <td style="font-size: 11.5px; color: #64748b;">Subtotal Masuk: <strong style="color: #16a34a; font-family: monospace;">+Rp {{ number_format($wInflow, 0, ',', '.') }}</strong></td>
                                            <td style="font-size: 11.5px; color: #64748b; text-align: center;">Subtotal Keluar: <strong style="color: #dc2626; font-family: monospace;">-Rp {{ number_format($wOutflow, 0, ',', '.') }}</strong></td>
                                            <td style="font-size: 12px; font-weight: 800; color: {{ $wNet >= 0 ? '#2563eb' : '#dc2626' }}; text-align: right; font-family: monospace;">
                                                Net: Rp {{ number_format($wNet, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    @endforeach
                @endif
            </td>
        </tr>

        <!-- 4. Highlight Pentotalan Konsolidasi Harian (Paling Bawah) -->
        <tr>
            <td style="padding: 0 30px 24px 30px;" class="content-padding">
                <div style="font-size: 13px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                    📊 TOTAL KONSOLIDASI FINANSIAL HARIAN
                </div>

                <table width="100%" style="margin-bottom: 16px;">
                    <!-- Total Inflow Card -->
                    <tr>
                        <td style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 5px solid #16a34a; border-radius: 10px; padding: 14px 16px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 700; color: #15803d; text-transform: uppercase; letter-spacing: 0.05em;">
                                            TOTAL UANG MASUK (TOTAL INCOME)
                                        </div>
                                        <div style="font-size: 22px; font-weight: 800; color: #15803d; font-family: monospace; margin-top: 2px;">
                                            +Rp {{ number_format($inflow, 0, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td height="8"></td></tr>
                    <!-- Total Outflow Card -->
                    <tr>
                        <td style="background-color: #fef2f2; border: 1px solid #fecaca; border-left: 5px solid #dc2626; border-radius: 10px; padding: 14px 16px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 700; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em;">
                                            TOTAL UANG KELUAR (TOTAL OUTCOME)
                                        </div>
                                        <div style="font-size: 22px; font-weight: 800; color: #b91c1c; font-family: monospace; margin-top: 2px;">
                                            -Rp {{ number_format($outflow, 0, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td height="8"></td></tr>
                    <!-- Total Net Profit Card -->
                    <tr>
                        <td style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-left: 5px solid #2563eb; border-radius: 10px; padding: 14px 16px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.05em;">
                                            TOTAL KEUNTUNGAN BERSIH KONSOLIDASI (NET PROFIT)
                                        </div>
                                        <div style="font-size: 24px; font-weight: 800; color: #1d4ed8; font-family: monospace; margin-top: 2px;">
                                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                                        </div>
                                        <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                                            Margin Laba Konsolidasi: <strong>{{ number_format($marginPct, 1) }}%</strong>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 5. Dokumen Lampiran Info & CTA Buttons -->
        <tr>
            <td style="padding: 0 30px 30px 30px; text-align: center;" class="content-padding">
                @if(class_exists('\Barryvdh\DomPDF\Facade\Pdf'))
                <div style="background-color: #eff6ff; border: 1px dashed #93c5fd; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; text-align: left;">
                    <table width="100%">
                        <tr>
                            <td style="width: 32px; vertical-align: middle;">
                                <span style="font-size: 24px;">📎</span>
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="font-size: 13px; font-weight: bold; color: #1e40af;">
                                    Dokumen PDF Resmi Telah Dilampirkan
                                </div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                    File PDF laporan lengkap terlampir di bawah email ini dan dapat langsung Anda unduh / simpan.
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                @endif

                <table width="100%">
                    <tr>
                        <td align="center">
                            <a href="{{ $dashboardUrl }}" target="_blank" class="btn-cta" style="margin-bottom: 8px;">
                                Buka Dashboard Investor &rarr;
                            </a>
                        </td>
                    </tr>
                </table>
                <p style="margin: 12px 0 0 0; font-size: 12px; color: #94a3b8;">
                    Akses grafik realtime, riwayat transaksi, dan rincian portofolio lengkap melalui dashboard web.
                </p>
            </td>
        </tr>

        <!-- 6. Footer -->
        <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 30px; text-align: center; color: #94a3b8; font-size: 12px; line-height: 1.6;">
                <p style="margin: 0 0 6px 0; font-weight: 600; color: #64748b;">
                    {{ $companyName }} &bull; Investor Relations
                </p>
                @if(!empty($setting->address))
                    <p style="margin: 0 0 6px 0;">{{ $setting->address }}</p>
                @endif
                <p style="margin: 0 0 8px 0;">
                    Email: <a href="mailto:{{ $companyEmail }}" style="color: #2563eb; text-decoration: none;">{{ $companyEmail }}</a> &bull; Telp: {{ $companyTelp }}
                </p>
                <p style="margin: 0; font-size: 11px; color: #cbd5e1;">
                    Email ini dibuat dan dikirim secara otomatis oleh sistem cronjob setiap hari. Anda menerima email ini karena terdaftar sebagai pemegang saham aktif di {{ $companyName }}.
                </p>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

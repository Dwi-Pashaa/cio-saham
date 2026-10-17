<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
        .btn-proof {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #1e293b !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
        }
        @media only screen and (max-width: 600px) {
            .content-padding {
                padding: 20px 16px !important;
            }
            .header-padding {
                padding: 24px 16px !important;
            }
        }
    </style>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 0;">
@php
    $companyName  = $setting->company_name ?? 'PT CIO Network Solution';
    $companyTelp  = $setting->telp ?? '6281234567890';
    $companyEmail = $setting->email ?? 'finance@cionetwork.id';

    // Warna tema berdasarkan jenis transaksi
    $themeColor = match($type) {
        'income'  => ['bg' => 'linear-gradient(135deg, #064e3b 0%, #059669 100%)', 'badge' => '#059669', 'badge_bg' => '#ecfdf5', 'tag' => 'SALDO MASUK (PEMASUKAN)'],
        'outcome' => ['bg' => 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)', 'badge' => '#dc2626', 'badge_bg' => '#fef2f2', 'tag' => 'SALDO KELUAR (PENGELUARAN)'],
        'saving'  => ['bg' => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)', 'badge' => '#2563eb', 'badge_bg' => '#eff6ff', 'tag' => 'ALOKASI TABUNGAN KAS'],
        'asset'   => ['bg' => 'linear-gradient(135deg, #581c87 0%, #7e22ce 100%)', 'badge' => '#7e22ce', 'badge_bg' => '#faf5ff', 'tag' => 'INVENTARIS & ASET BARU'],
        default   => ['bg' => 'linear-gradient(135deg, #0f172a 0%, #334155 100%)', 'badge' => '#3b82f6', 'badge_bg' => '#eff6ff', 'tag' => 'NOTIFIKASI SISTEM'],
    };
@endphp

<div class="wrapper">
    <table class="main" align="center">
        <!-- 1. Header Banner -->
        <tr>
            <td style="background: {{ $themeColor['bg'] }}; padding: 30px 28px; text-align: center;" class="header-padding">
                <table width="100%">
                    <tr>
                        <td align="center">
                            @if(!empty($setting->logo))
                                <img src="{{ asset('storage/' . $setting->logo) }}" alt="{{ $companyName }}" width="56" style="margin-bottom: 12px; border-radius: 8px;">
                            @endif
                            <div style="display: inline-block; padding: 4px 12px; background: rgba(255, 255, 255, 0.15); border-radius: 20px; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 10px;">
                                {{ $themeColor['tag'] }}
                            </div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.3;">
                                {{ $title }}
                            </h1>
                            <p style="margin: 6px 0 0 0; color: rgba(255, 255, 255, 0.85); font-size: 13px;">
                                {{ $companyName }} • Notifikasi Otomatis Sistem
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 2. Body Content -->
        <tr>
            <td style="padding: 28px 28px 20px 28px;" class="content-padding">
                <p style="margin: 0 0 18px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                    Halo Bapak/Ibu Pemegang Saham & Manajemen,<br>
                    Berikut adalah rincian mutasi transaksi kas / aset terbaru yang baru saja tercatat pada sistem:
                </p>

                <!-- Transaction Details Table -->
                <table width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    @foreach($details as $label => $value)
                        <tr>
                            <td style="padding: 11px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600; width: 42%; vertical-align: top;">
                                {{ $label }}
                            </td>
                            <td style="padding: 11px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; width: 58%; vertical-align: top;">
                                {!! nl2br(e($value)) !!}
                            </td>
                        </tr>
                    @endforeach
                </table>

                <!-- Balance Comparison (If available) -->
                @if(!is_null($previousBalance) && !is_null($currentBalance))
                    <table width="100%" style="background-color: #f1f5f9; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid {{ $themeColor['badge'] }};">
                        <tr>
                            <td style="padding: 14px 16px;">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 4px;">
                                    Ringkasan Posisi Saldo Kas Operasional
                                </div>
                                <table width="100%">
                                    <tr>
                                        <td style="font-size: 12px; color: #475569;">Saldo Sebelumnya:</td>
                                        <td align="right" style="font-size: 13px; font-weight: 600; font-family: monospace; color: #64748b;">
                                            Rp {{ number_format($previousBalance, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px; color: #0f172a; font-weight: 700; padding-top: 4px;">Saldo Kas Terbaru:</td>
                                        <td align="right" style="font-size: 15px; font-weight: 800; font-family: monospace; color: #059669; padding-top: 4px;">
                                            Rp {{ number_format($currentBalance, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                @endif

                <!-- Bukti Transaksi Link (If available) -->
                @if(!empty($proofUrl) && $proofUrl !== '-')
                    <div style="text-align: center; margin-bottom: 24px; padding: 14px; background: #faf5ff; border: 1px dashed #d8b4fe; border-radius: 10px;">
                        <div style="font-size: 12px; color: #6b21a8; font-weight: 600; margin-bottom: 8px;">
                            Berkas Bukti Pembayaran / Foto Lampiran Tersedia:
                        </div>
                        <a href="{{ $proofUrl }}" target="_blank" class="btn-proof" style="display: inline-block;">
                            📎 Buka Bukti Transaksi / Lampiran di Browser ↗
                        </a>
                    </div>
                @endif

                <!-- CTA Button -->
                <div style="text-align: center; margin: 26px 0 10px 0;">
                    <a href="{{ $dashboardUrl }}" class="btn-cta">
                        Buka Dashboard Portal Finansial
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
                    WhatsApp: {{ $companyTelp }} • Email: {{ $companyEmail }}<br>
                    Email ini dikirim secara otomatis oleh sistem notifikasi inventaris & kas PT CIO.
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

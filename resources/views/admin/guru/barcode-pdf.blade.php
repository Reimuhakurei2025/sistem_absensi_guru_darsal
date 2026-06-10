<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Barcode - {{ $guru->nama_lengkap }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        .card {
            border: 2px solid #2E7D32;
            border-radius: 12px;
            padding: 25px 30px;
            max-width: 500px;
            margin: 60px auto 0 auto;
            text-align: center;
            background: #ffffff;
        }

        /* ============== KOP CARD ============== */
        .card-header {
            border-bottom: 2px solid #2E7D32;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .card-header img {
            width: 70px;
            height: 70px;
            margin-bottom: 6px;
        }
        .card-header h1 {
            font-size: 14pt;
            color: #1B5E20;
            margin: 4px 0 2px 0;
            font-weight: bold;
        }
        .card-header .alamat {
            font-size: 8pt;
            color: #666;
            margin: 0;
        }
        .card-header .label {
            font-size: 10pt;
            color: #2E7D32;
            font-weight: bold;
            margin-top: 6px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        /* ============== QR CODE WRAP ============== */
        .qr-wrap {
            padding: 18px;
            display: inline-block;
            background: #ffffff;
            border: 2px solid #E8F5E9;
            border-radius: 12px;
            margin: 8px 0 18px 0;
        }

        .qr-wrap img {
            width: 220px;
            height: 220px;
            display: block;
        }

        /* ============== INFO GURU ============== */
        .guru-info {
            margin-bottom: 16px;
        }
        .guru-info h2 {
            font-size: 14pt;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 4px 0;
        }
        .guru-info .jabatan {
            font-size: 10.5pt;
            color: #555;
            margin: 0 0 6px 0;
        }
        .guru-info .nip {
            font-size: 9pt;
            color: #888;
            font-family: 'Courier New', monospace;
        }

        /* ============== TOKEN BOX ============== */
        .token-box {
            background: #F3F4F6;
            border-radius: 8px;
            padding: 10px 12px;
            margin: 12px 0;
        }
        .token-box .label {
            font-size: 7.5pt;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .token-box .value {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            font-weight: bold;
            color: #1f2937;
            letter-spacing: 1px;
        }

        /* ============== FOOTER ============== */
        .card-footer {
            border-top: 1px solid #E5E7EB;
            padding-top: 12px;
            margin-top: 8px;
            font-size: 8.5pt;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="card">

        {{-- Header --}}
        <div class="card-header">
            <img src="{{ public_path('images/logo-darussalam.png') }}" alt="Logo">
            <h1>SMP TERPADU DARUSSALAM</h1>
            <p class="alamat">Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok</p>
            <div class="label">Kartu Absensi Guru</div>
        </div>

        {{-- QR Code (PNG base64 dari GD, atau SVG base64 fallback) --}}
        <div class="qr-wrap">
            <img src="{{ $qrDataUri }}" alt="QR Code">
        </div>

        {{-- Info Guru --}}
        <div class="guru-info">
            <h2>{{ $guru->nama_lengkap }}</h2>
            <p class="jabatan">
                {{ $guru->jabatan ?: 'Guru' }}{{ $guru->mata_pelajaran ? ' · ' . $guru->mata_pelajaran : '' }}
            </p>
            @if($guru->nip)
                <p class="nip">NIP: {{ $guru->nip }}</p>
            @endif
        </div>

        {{-- Token --}}
        <div class="token-box">
            <div class="label">Token Barcode</div>
            <div class="value">{{ $guru->barcode_token }}</div>
        </div>

        {{-- Footer --}}
        <div class="card-footer">
            Scan QR Code ini di halaman absensi sistem untuk mencatat kehadiran
        </div>
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Barcode Semua Guru</title>
    <style>
        @page {
            margin: 1.2cm 1cm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
            font-size: 9pt;
        }

        /* ============== HEADER HALAMAN ============== */
        .page-header {
            text-align: center;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2E7D32;
        }
        .page-header img {
            width: 50px;
            height: 50px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .page-header .title-block {
            display: inline-block;
            vertical-align: middle;
            text-align: left;
        }
        .page-header h1 {
            font-size: 13pt;
            color: #1B5E20;
            margin: 0;
            font-weight: bold;
            line-height: 1.2;
        }
        .page-header .subtitle {
            font-size: 9pt;
            color: #666;
            margin: 2px 0 0 0;
        }

        /* ============== GRID CARDS ============== */
        .cards-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }
        .cards-grid td {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }

        .barcode-card {
            border: 1.5px dashed #BBBBBB;
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
            text-align: center;
        }

        /* ============== KOP MINI ============== */
        .card-kop {
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .card-kop img {
            width: 26px;
            height: 26px;
            vertical-align: middle;
            margin-right: 4px;
        }
        .card-kop .nama-sekolah {
            display: inline-block;
            vertical-align: middle;
            font-size: 8pt;
            font-weight: bold;
            color: #1B5E20;
            line-height: 1.1;
            text-align: left;
        }
        .card-kop .label {
            font-size: 6.5pt;
            color: #888;
            margin-top: 1px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ============== QR MINI (PNG/SVG data URI) ============== */
        .qr-mini {
            padding: 4px;
            display: inline-block;
            background: #ffffff;
            border: 1px solid #E8F5E9;
            border-radius: 4px;
            margin: 4px 0 6px 0;
        }
        .qr-mini img {
            width: 110px;
            height: 110px;
            display: block;
        }

        /* ============== INFO GURU ============== */
        .info-guru .nama {
            font-size: 9pt;
            font-weight: bold;
            color: #1f2937;
            line-height: 1.2;
            margin: 0;
        }
        .info-guru .jabatan {
            font-size: 8pt;
            color: #666;
            margin: 2px 0 0 0;
        }
        .info-guru .nip {
            font-size: 7pt;
            color: #888;
            font-family: 'Courier New', monospace;
            margin: 1px 0 0 0;
        }
        .info-guru .token {
            font-size: 7pt;
            color: #2E7D32;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            margin-top: 4px;
            background: #F3F4F6;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }
    </style>
</head>
<body>

    {{-- Header halaman --}}
    <div class="page-header">
        <img src="{{ public_path('images/logo-darussalam.png') }}" alt="Logo">
        <div class="title-block">
            <h1>KARTU ABSENSI GURU</h1>
            <p class="subtitle">
                SMP Terpadu Darussalam · Total {{ $gurus->count() }} kartu
            </p>
        </div>
    </div>

    {{-- Grid kartu 2 kolom --}}
    @php
        $chunks = $gurus->chunk(2);
    @endphp

    <table class="cards-grid">
        @foreach($chunks as $pair)
            <tr>
                @foreach($pair as $guru)
                    <td>
                        <div class="barcode-card">
                            {{-- Kop mini --}}
                            <div class="card-kop">
                                <img src="{{ public_path('images/logo-darussalam.png') }}" alt="">
                                <span class="nama-sekolah">
                                    SMP TERPADU<br>DARUSSALAM
                                </span>
                                <div class="label">Kartu Absensi Guru</div>
                            </div>

                            {{-- QR Code (data URI) --}}
                            <div class="qr-mini">
                                <img src="{{ $qrCodes[$guru->id_guru] }}" alt="QR">
                            </div>

                            {{-- Info --}}
                            <div class="info-guru">
                                <p class="nama">{{ $guru->nama_lengkap }}</p>
                                <p class="jabatan">
                                    {{ $guru->jabatan ?: 'Guru' }}
                                    @if($guru->mata_pelajaran)
                                        · {{ $guru->mata_pelajaran }}
                                    @endif
                                </p>
                                @if($guru->nip)
                                    <p class="nip">NIP: {{ $guru->nip }}</p>
                                @endif
                                <div class="token">{{ $guru->barcode_token }}</div>
                            </div>
                        </div>
                    </td>
                @endforeach
                {{-- Jika ganjil, tambah cell kosong --}}
                @if($pair->count() === 1)
                    <td>&nbsp;</td>
                @endif
            </tr>
        @endforeach
    </table>

</body>
</html>

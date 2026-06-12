<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        /* ============== PAGE SETUP ============== */
        @page {
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* ============== KOP SEKOLAH ============== */
        .kop {
            width: 100%;
            margin-bottom: 8px;
        }
        .kop td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-logo {
            width: 80px;
            text-align: center;
        }
        .kop-logo img {
            width: 70px;
            height: 70px;
        }
        .kop-text {
            text-align: center;
            padding-left: 8px;
        }
        .kop-text h1 {
            font-size: 18pt;
            color: #1B5E20;
            margin: 0;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-text .alamat {
            font-size: 10pt;
            color: #555;
            margin: 2px 0 0 0;
        }
        .kop-text .lokasi {
            font-size: 9.5pt;
            color: #666;
            margin: 1px 0 0 0;
        }

        .kop-divider {
            border-bottom: 3px double #2E7D32;
            margin: 6px 0 14px 0;
            height: 4px;
        }

        /* ============== JUDUL LAPORAN ============== */
        .judul-laporan {
            text-align: center;
            margin-bottom: 14px;
        }
        .judul-laporan h2 {
            font-size: 13pt;
            color: #2E7D32;
            margin: 0 0 2px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .judul-laporan .periode {
            font-size: 11pt;
            color: #555;
            font-style: italic;
        }

        /* ============== INFO BAR ============== */
        .info-bar {
            font-size: 9pt;
            color: #555;
            margin: 0 0 8px 0;
            font-style: italic;
        }

        /* ============== TABEL ============== */
        table.rekap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.rekap th,
        table.rekap td {
            border: 1px solid #999;
            padding: 5px 6px;
            font-size: 9pt;
            vertical-align: middle;
        }
        table.rekap thead th {
            background: #2E7D32;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.rekap tbody tr:nth-child(even) td:not(.bg-status):not(.bg-total) {
            background: #fafafa;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .fw-bold     { font-weight: bold; }

        /* Kolom widths */
        .col-no    { width: 4%; }
        .col-nama  { width: 22%; }
        .col-nip   { width: 12%; }
        .col-jab   { width: 18%; }
        .col-status{ width: 7%; }
        .col-total { width: 9%; }

        .bg-hadir { background-color: #d1fae5; }
        .bg-izin  { background-color: #fef3c7; }
        .bg-sakit { background-color: #dbeafe; }
        .bg-alpa  { background-color: #fee2e2; }
        .bg-total { background-color: #f3f4f6; font-weight: bold; }

        tfoot td {
            background: #2E7D32 !important;
            color: white !important;
            font-weight: bold;
            text-align: center;
        }
        tfoot .total-label {
            text-align: right;
            padding-right: 10px;
        }
        tfoot .total-grand {
            background: #1B5E20 !important;
        }

        /* ============== TANDA TANGAN ============== */
        .signature {
            margin-top: 40px;
            width: 100%;
        }
        .signature td {
            border: none;
            padding: 2px 0;
            font-size: 10pt;
            vertical-align: top;
        }
        .signature .sign-block {
            text-align: center;
            width: 35%;
        }
        .signature .sign-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 10pt;
        }
        .signature .sign-nip {
            font-size: 9pt;
            color: #555;
        }
        .signature .sign-space {
            height: 70px;
        }

        /* ============== FOOTER PAGE ============== */
        .page-footer {
            position: fixed;
            bottom: 0.5cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }
    </style>
</head>
<body>

    {{-- ============== KOP SEKOLAH ============== --}}
    <table class="kop">
        <tr>
            <td class="kop-logo">
                <img src="{{ public_path('images/logo-darussalam.png') }}" alt="Logo">
            </td>
            <td class="kop-text">
                <h1>SMP TERPADU DARUSSALAM</h1>
                <p class="alamat">Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok</p>
                <p class="lokasi">Bojongsari, Depok</p>
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- ============== JUDUL ============== --}}
    <div class="judul-laporan">
        <h2>LAPORAN ABSENSI GURU</h2>
        <div class="periode">Periode: {{ $namaBulan }} {{ $tahun }}</div>
    </div>

    {{-- ============== INFO BAR ============== --}}
    @php
        $totalHadir = $gurus->sum('jumlah_hadir');
        $totalIzin  = $gurus->sum('jumlah_izin');
        $totalSakit = $gurus->sum('jumlah_sakit');
        $totalAlpa  = $gurus->sum('jumlah_alpa');
        $totalAll   = $totalHadir + $totalIzin + $totalSakit + $totalAlpa;
    @endphp

    <div class="info-bar">
        Total guru aktif: <strong>{{ $gurus->count() }}</strong>
        &nbsp;·&nbsp; Total kehadiran tercatat: <strong>{{ $totalAll }}</strong>
    </div>

    {{-- ============== TABEL REKAP ============== --}}
    <table class="rekap">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Nama Guru</th>
                <th style="width: 22%;">Jabatan / Mapel</th>
                <th style="width: 9%;">Hadir</th>
                <th style="width: 9%;">Izin</th>
                <th style="width: 9%;">Sakit</th>
                <th style="width: 9%;">Alpa</th>
                <th style="width: 10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gurus as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row->guru->nama_lengkap }}</td>
                    <td>{{ $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-') }}</td>
                    <td class="text-center bg-status bg-hadir">{{ $row->jumlah_hadir }}</td>
                    <td class="text-center bg-status bg-izin">{{ $row->jumlah_izin }}</td>
                    <td class="text-center bg-status bg-sakit">{{ $row->jumlah_sakit }}</td>
                    <td class="text-center bg-status bg-alpa">{{ $row->jumlah_alpa }}</td>
                    <td class="text-center bg-total">{{ $row->total_absensi }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total-label">TOTAL</td>
                <td>{{ $totalHadir }}</td>
                <td>{{ $totalIzin }}</td>
                <td>{{ $totalSakit }}</td>
                <td>{{ $totalAlpa }}</td>
                <td class="total-grand">{{ $totalAll }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ============== TANDA TANGAN ============== --}}
    <table class="signature">
        <tr>
            <td style="width: 65%;"></td>
            <td class="sign-block">
                Depok, {{ $tanggalCetak }}<br>
                Kepala Sekolah,
                <div class="sign-space"></div>
                <div class="sign-name">{{ $kepsek->nama_lengkap }}</div>
                @if($kepsek->nip)
                    <div class="sign-nip">NIP. {{ $kepsek->nip }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ============== PAGE FOOTER ============== --}}
    <div class="page-footer">
        Dicetak pada {{ $tanggalCetak }} · Sistem Absensi SMP Terpadu Darussalam
    </div>

</body>
</html>

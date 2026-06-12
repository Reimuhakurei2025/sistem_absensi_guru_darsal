<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian {{ $tanggalLabel }}</title>
    <style>
        @page { margin: 1.5cm 1.5cm; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1f2937; line-height: 1.4; margin: 0; }

        /* KOP */
        .kop { width: 100%; margin-bottom: 6px; }
        .kop td { vertical-align: middle; padding: 0; }
        .kop-logo { width: 70px; text-align: center; }
        .kop-logo img { width: 60px; height: 60px; }
        .kop-text { text-align: center; padding-left: 8px; }
        .kop-text h1 { font-size: 16pt; color: #1B5E20; margin: 0; }
        .kop-text .alamat { font-size: 9pt; color: #555; margin: 2px 0 0 0; }
        .kop-divider { border-bottom: 3px double #2E7D32; margin: 6px 0 12px 0; }

        /* JUDUL */
        .judul { text-align: center; margin-bottom: 12px; }
        .judul h2 { font-size: 13pt; color: #2E7D32; margin: 0 0 2px 0; text-transform: uppercase; }
        .judul .tanggal { font-size: 11pt; color: #333; }

        /* RINGKASAN */
        .ringkasan { width: 100%; margin-bottom: 10px; }
        .ringkasan td { text-align: center; padding: 6px 4px; font-size: 9pt; border: 1px solid #ddd; }
        .ringkasan .label { font-weight: bold; font-size: 8pt; color: #555; text-transform: uppercase; }
        .ringkasan .angka { font-size: 14pt; font-weight: bold; }
        .r-hadir { background: #d1fae5; color: #166534; }
        .r-izin  { background: #fef3c7; color: #92400e; }
        .r-sakit { background: #dbeafe; color: #1e40af; }
        .r-alpa  { background: #fee2e2; color: #991b1b; }
        .r-belum { background: #f3f4f6; color: #6b7280; }

        /* TABEL */
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th, table.data td { border: 1px solid #999; padding: 5px 6px; font-size: 9pt; vertical-align: middle; }
        table.data thead th { background: #2E7D32; color: white; font-size: 8.5pt; text-align: center; text-transform: uppercase; letter-spacing: 0.3px; }
        .text-center { text-align: center; }

        /* STATUS BADGE */
        .st-hadir { background: #d1fae5; color: #166534; font-weight: bold; text-align: center; }
        .st-izin  { background: #fef3c7; color: #92400e; font-weight: bold; text-align: center; }
        .st-sakit { background: #dbeafe; color: #1e40af; font-weight: bold; text-align: center; }
        .st-alpa  { background: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
        .st-belum { background: #f9fafb; color: #9ca3af; font-style: italic; text-align: center; }

        /* TANDA TANGAN */
        .signature { margin-top: 30px; width: 100%; }
        .signature td { border: none; padding: 2px 0; font-size: 10pt; }
        .signature .sign-block { text-align: center; width: 40%; }
        .signature .sign-name { font-weight: bold; text-decoration: underline; }
        .signature .sign-nip { font-size: 9pt; color: #555; }

        /* FOOTER */
        .page-footer { position: fixed; bottom: 0.5cm; left: 0; right: 0; text-align: center; font-size: 7.5pt; color: #aaa; }
    </style>
</head>
<body>

    {{-- KOP --}}
    <table class="kop">
        <tr>
            <td class="kop-logo"><img src="{{ public_path('images/logo-darussalam.png') }}" alt="Logo"></td>
            <td class="kop-text">
                <h1>SMP TERPADU DARUSSALAM</h1>
                <p class="alamat">Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok</p>
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- JUDUL --}}
    <div class="judul">
        <h2>Laporan Absensi Harian</h2>
        <div class="tanggal">{{ $tanggalLabel }}</div>
    </div>

    {{-- RINGKASAN --}}
    @php
        $hadir = $data->where('status', 'hadir')->count();
        $izin  = $data->where('status', 'izin')->count();
        $sakit = $data->where('status', 'sakit')->count();
        $alpa  = $data->where('status', 'alpa')->count();
        $belum = $data->where('status', 'belum')->count();
    @endphp
    <table class="ringkasan">
        <tr>
            <td class="r-hadir"><div class="label">Hadir</div><div class="angka">{{ $hadir }}</div></td>
            <td class="r-izin"><div class="label">Izin</div><div class="angka">{{ $izin }}</div></td>
            <td class="r-sakit"><div class="label">Sakit</div><div class="angka">{{ $sakit }}</div></td>
            <td class="r-alpa"><div class="label">Alpa</div><div class="angka">{{ $alpa }}</div></td>
            <td class="r-belum"><div class="label">Belum</div><div class="angka">{{ $belum }}</div></td>
        </tr>
    </table>

    {{-- TABEL --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 28%;">Nama Guru</th>
                <th style="width: 22%;">Jabatan / Mapel</th>
                <th style="width: 11%;">Status</th>
                <th style="width: 12%;">Jam Masuk</th>
                <th style="width: 10%;">Metode</th>
                <th style="width: 12%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row->guru->nama_lengkap }}</td>
                    <td>{{ $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-') }}</td>
                    <td class="st-{{ $row->status }}">{{ $row->status === 'belum' ? '-' : ucfirst($row->status) }}</td>
                    <td class="text-center" style="font-family: 'Courier New', monospace; font-size: 9pt;">
                        {{ $row->jam_masuk ?: '-' }}
                    </td>
                    <td class="text-center">
                        @if($row->input_method === 'scan') Scan QR
                        @elseif($row->input_method === 'manual') Manual
                        @else - @endif
                    </td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="signature">
        <tr><td style="width: 60%;"></td>
            <td class="sign-block">
                Depok, {{ $tanggalCetak }}<br>Kepala Sekolah,
                <div style="height: 60px;"></div>
                <div class="sign-name">{{ $kepsek->nama_lengkap ?? '-' }}</div>
                @if($kepsek->nip)<div class="sign-nip">NIP. {{ $kepsek->nip }}</div>@endif
            </td>
        </tr>
    </table>

    <div class="page-footer">Dicetak pada {{ $tanggalCetak }} · Sistem Absensi SMP Terpadu Darussalam</div>
</body>
</html>

<?php

namespace App\Services;

use App\Models\Kepsek;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * LaporanWordGenerator
 *
 * Generate laporan absensi bulanan dalam format Word (.docx).
 *
 * Spesifikasi format:
 *  - Kertas A4 Landscape
 *  - Kop sekolah (logo + nama + alamat)
 *  - Tabel rekap dengan kolom warna sesuai status
 *  - Footer total ter-merge sampai full lebar tabel (fix issue lama)
 *  - Tanda tangan Kepala Sekolah otomatis
 *  - Auto-repeat header tabel di setiap halaman
 */
class LaporanWordGenerator
{
    protected PhpWord $phpWord;
    protected $section;

    // ============== KONSTANTA DESAIN ==============
    private const COLOR_PRIMARY  = '2E7D32';
    private const COLOR_DARK     = '1B5E20';
    private const COLOR_TEXT     = '1F2937';
    private const COLOR_HADIR    = 'D1FAE5';
    private const COLOR_IZIN    = 'FEF3C7';
    private const COLOR_SAKIT   = 'DBEAFE';
    private const COLOR_ALPA    = 'FEE2E2';
    private const COLOR_GRAY     = 'F3F4F6';
    private const COLOR_BORDER   = 'BBBBBB';

    /**
     * LEBAR KOLOM (twip; 1 cm ≈ 567 twip).
     * Total = 16000 twip ≈ 28.2 cm (cocok A4 landscape margin 1.27cm).
     * Penjumlahan: 600 + 3800 + 2000 + 3000 + 1100 + 1100 + 1100 + 1100 + 1200 = 15000
     */
    // Kolom widths (tanpa NIP)
    private const COL_NO     = 600;
    private const COL_NAMA   = 4200;
    private const COL_JABMAP = 3600;
    private const COL_STATUS = 1200;   // 4x (hadir/izin/sakit/alpa)
    private const COL_TOTAL  = 1400;
    private const TOTAL_COLS = 8;

    public function __construct()
    {
        $this->phpWord = new PhpWord();

        // Default styling
        $this->phpWord->setDefaultFontName('Calibri');
        $this->phpWord->setDefaultFontSize(10);

        // Properties dokumen
        $this->phpWord->getDocInfo()
            ->setCreator('SMP Terpadu Darussalam')
            ->setTitle('Laporan Absensi Guru');

        // Section: A4 Landscape (orientasi: lebar 16838 x tinggi 11906 twip)
        $this->section = $this->phpWord->addSection([
            'orientation'  => 'landscape',
            'pageSizeW'    => 16838,    // A4 landscape width
            'pageSizeH'    => 11906,
            'marginTop'    => 720,      // ~1.27 cm
            'marginBottom' => 720,
            'marginLeft'   => 900,      // ~1.59 cm
            'marginRight'  => 900,
        ]);
    }

    /**
     * Entry point: generate file Word dan return download response.
     */
    public function generate(
        iterable $gurus,
        int $bulan,
        int $tahun,
        Kepsek $kepsek,
        string $namaBulan,
        string $tanggalCetak
    ): StreamedResponse {
        $this->renderHeader($namaBulan, $tahun);
        $this->renderInfoBar($gurus);
        $this->renderTable($gurus);
        $this->renderSignature($kepsek, $tanggalCetak);

        $filename = "Laporan_Absensi_{$namaBulan}_{$tahun}.docx";

        return response()->streamDownload(function () {
            $writer = IOFactory::createWriter($this->phpWord, 'Word2007');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    // =====================================================================
    // KOP SEKOLAH
    // =====================================================================
    protected function renderHeader(string $namaBulan, int $tahun): void
    {
        $logoPath = public_path('images/logo-darussalam.png');

        // Tabel 2 kolom: logo (kiri) + teks (kanan)
        $headerTable = $this->section->addTable([
            'borderSize' => 0,
            'cellMargin' => 80,
            'width'      => 100 * 50,
            'unit'       => 'pct',
        ]);

        $headerTable->addRow();

        // Cell Logo
        $cellLogo = $headerTable->addCell(1600, ['valign' => 'center']);
        if (file_exists($logoPath)) {
            $cellLogo->addImage($logoPath, [
                'width'     => 65,
                'height'    => 65,
                'alignment' => Jc::CENTER,
            ]);
        }

        // Cell Teks Kop
        $cellText = $headerTable->addCell(13400, ['valign' => 'center']);

        $cellText->addText('SMP TERPADU DARUSSALAM',
            ['bold' => true, 'size' => 18, 'color' => self::COLOR_DARK],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]);

        $cellText->addText('Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok',
            ['size' => 10, 'color' => '555555'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        $cellText->addText('Bojongsari, Depok',
            ['size' => 10, 'color' => '555555'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        // Garis pembatas tebal (di luar tabel)
        $this->section->addText('',
            ['size' => 1],
            ['border-top' => '#2E7D32', 'border-bottom-size' => 18, 'spaceAfter' => 0]);

        // Judul laporan
        $this->section->addText(
            "LAPORAN ABSENSI GURU",
            ['bold' => true, 'size' => 14, 'color' => self::COLOR_PRIMARY],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 240, 'spaceAfter' => 0]
        );

        $this->section->addText(
            "Periode: {$namaBulan} {$tahun}",
            ['size' => 11, 'color' => '555555', 'italic' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );
    }

    // =====================================================================
    // INFO BAR (total guru + total absensi)
    // =====================================================================
    protected function renderInfoBar(iterable $gurus): void
    {
        $totalGuru = 0;
        $totalAll  = 0;
        foreach ($gurus as $row) {
            $totalGuru++;
            $totalAll += $row->total_absensi;
        }

        $this->section->addText(
            "Total guru aktif: {$totalGuru}    ·    Total kehadiran tercatat: {$totalAll}",
            ['size' => 9, 'color' => '555555', 'italic' => true],
            ['spaceAfter' => 100]
        );
    }

    // =====================================================================
    // TABEL REKAP (header + body + footer total)
    // =====================================================================
    protected function renderTable(iterable $gurus): void
    {
        $table = $this->section->addTable([
            'borderColor'   => self::COLOR_BORDER,
            'borderSize'    => 6,
            'cellMargin'    => 60,
            'alignment'     => Jc::CENTER,
            'width'         => 100 * 50,
            'unit'          => 'pct',
        ]);

        // -------------- HEADER ROW --------------
        $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 10];
        $headerCellStyle = [
            'bgColor'     => self::COLOR_PRIMARY,
            'valign'      => 'center',
            'tblHeader'   => true,   // ulangi di setiap halaman
        ];
        $headerParaStyle = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];

        $table->addRow(400, ['tblHeader' => true]);
        $table->addCell(self::COL_NO,     $headerCellStyle)->addText('No',           $headerFont, $headerParaStyle);
        $table->addCell(self::COL_NAMA,   $headerCellStyle)->addText('Nama Guru',    $headerFont, $headerParaStyle);
        $table->addCell(self::COL_JABMAP, $headerCellStyle)->addText('Jabatan / Mapel', $headerFont, $headerParaStyle);
        $table->addCell(self::COL_STATUS, $headerCellStyle)->addText('Hadir',        $headerFont, $headerParaStyle);
        $table->addCell(self::COL_STATUS, $headerCellStyle)->addText('Izin',         $headerFont, $headerParaStyle);
        $table->addCell(self::COL_STATUS, $headerCellStyle)->addText('Sakit',        $headerFont, $headerParaStyle);
        $table->addCell(self::COL_STATUS, $headerCellStyle)->addText('Alpa',         $headerFont, $headerParaStyle);
        $table->addCell(self::COL_TOTAL,  $headerCellStyle)->addText('Total',        $headerFont, $headerParaStyle);

        // -------------- BODY ROWS --------------
        $bodyFont   = ['size' => 9, 'color' => self::COLOR_TEXT];
        $centerPara = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];
        $leftPara   = ['alignment' => Jc::START,  'spaceAfter' => 0];

        $totalHadir = $totalIzin = $totalSakit = $totalAlpa = $totalAll = 0;
        $i = 0;

        foreach ($gurus as $row) {
            $i++;
            $totalHadir += $row->jumlah_hadir;
            $totalIzin  += $row->jumlah_izin;
            $totalSakit += $row->jumlah_sakit;
            $totalAlpa  += $row->jumlah_alpa;
            $totalAll   += $row->total_absensi;

            $table->addRow();

            $table->addCell(self::COL_NO)
                  ->addText((string)$i, $bodyFont, $centerPara);

            $table->addCell(self::COL_NAMA)
                  ->addText($row->guru->nama_lengkap, $bodyFont, $leftPara);

            $table->addCell(self::COL_JABMAP)
                  ->addText($row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-'),
                            $bodyFont, $leftPara);

            $table->addCell(self::COL_STATUS, ['bgColor' => self::COLOR_HADIR])
                  ->addText((string)$row->jumlah_hadir, $bodyFont, $centerPara);

            $table->addCell(self::COL_STATUS, ['bgColor' => self::COLOR_IZIN])
                  ->addText((string)$row->jumlah_izin, $bodyFont, $centerPara);

            $table->addCell(self::COL_STATUS, ['bgColor' => self::COLOR_SAKIT])
                  ->addText((string)$row->jumlah_sakit, $bodyFont, $centerPara);

            $table->addCell(self::COL_STATUS, ['bgColor' => self::COLOR_ALPA])
                  ->addText((string)$row->jumlah_alpa, $bodyFont, $centerPara);

            $table->addCell(self::COL_TOTAL, ['bgColor' => self::COLOR_GRAY])
                  ->addText((string)$row->total_absensi,
                            ['size' => 9, 'bold' => true, 'color' => self::COLOR_TEXT],
                            $centerPara);
        }

        // -------------- FOOTER TOTAL (FIX: pakai gridSpan untuk colspan) --------------
        $totalFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 10];
        $totalCellStyle = [
            'bgColor' => self::COLOR_PRIMARY,
            'valign'  => 'center',
        ];

        $table->addRow();

        // Merge 3 kolom pertama (No + Nama + Jabatan/Mapel) jadi 1 cell "TOTAL"
        $mergeCellStyle = array_merge($totalCellStyle, ['gridSpan' => 3]);
        $table->addCell(
            self::COL_NO + self::COL_NAMA + self::COL_JABMAP,
            $mergeCellStyle
        )->addText('TOTAL', $totalFont, ['alignment' => Jc::END, 'spaceAfter' => 0]);

        // 4 kolom status (warna sama hijau primary)
        $table->addCell(self::COL_STATUS, $totalCellStyle)
              ->addText((string)$totalHadir, $totalFont, $centerPara);
        $table->addCell(self::COL_STATUS, $totalCellStyle)
              ->addText((string)$totalIzin, $totalFont, $centerPara);
        $table->addCell(self::COL_STATUS, $totalCellStyle)
              ->addText((string)$totalSakit, $totalFont, $centerPara);
        $table->addCell(self::COL_STATUS, $totalCellStyle)
              ->addText((string)$totalAlpa, $totalFont, $centerPara);

        // Kolom total grand
        $table->addCell(self::COL_TOTAL, array_merge($totalCellStyle, ['bgColor' => self::COLOR_DARK]))
              ->addText((string)$totalAll, $totalFont, $centerPara);

        $this->section->addTextBreak(1);
    }

    // =====================================================================
    // TANDA TANGAN
    // =====================================================================
    protected function renderSignature(Kepsek $kepsek, string $tanggalCetak): void
    {
        $signTable = $this->section->addTable(['borderSize' => 0]);
        $signTable->addRow();

        // Kolom kiri kosong
        $signTable->addCell(9500);

        // Kolom kanan: tanda tangan
        $cellSign = $signTable->addCell(6000);

        $signFont   = ['size' => 10, 'color' => self::COLOR_TEXT];
        $paraCenter = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];

        $cellSign->addText("Depok, {$tanggalCetak}", $signFont, $paraCenter);
        $cellSign->addText("Kepala Sekolah,", $signFont, $paraCenter);

        // Spasi untuk tanda tangan (5 baris kosong)
        for ($i = 0; $i < 5; $i++) {
            $cellSign->addTextBreak();
        }

        $cellSign->addText(
            $kepsek->nama_lengkap,
            ['size' => 10, 'bold' => true, 'underline' => 'single', 'color' => self::COLOR_TEXT],
            $paraCenter
        );

        if ($kepsek->nip) {
            $cellSign->addText(
                'NIP. ' . $kepsek->nip,
                ['size' => 9, 'color' => '555555'],
                $paraCenter
            );
        }
    }
}

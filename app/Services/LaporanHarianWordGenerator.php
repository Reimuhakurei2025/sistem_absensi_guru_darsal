<?php

namespace App\Services;

use App\Models\Kepsek;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanHarianWordGenerator
{
    protected PhpWord $phpWord;
    protected $section;

    private const COLOR_PRIMARY = '2E7D32';
    private const COLOR_DARK    = '1B5E20';
    private const PUTIH         = 'FFFFFF';

    // A4 portrait kolom (7 kolom, total ~10000 twip)
    private const COL_NO     = 500;
    private const COL_NAMA   = 2600;
    private const COL_JABMAP = 2000;
    private const COL_STATUS = 1000;
    private const COL_JAM    = 1200;
    private const COL_METODE = 1000;
    private const COL_KET    = 1700;

    public function __construct()
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->setDefaultFontName('Calibri');
        $this->phpWord->setDefaultFontSize(10);

        // A4 Portrait
        $this->section = $this->phpWord->addSection([
            'orientation'  => 'portrait',
            'marginTop'    => 1080,
            'marginBottom' => 1080,
            'marginLeft'   => 1080,
            'marginRight'  => 1080,
        ]);
    }

    public function generate(iterable $data, string $tanggal, ?Kepsek $kepsek, string $tanggalLabel, string $tanggalCetak): StreamedResponse
    {
        $this->renderHeader($tanggalLabel);
        $this->renderTable($data);
        $this->renderSignature($kepsek, $tanggalCetak);

        $filename = "Laporan_Harian_{$tanggal}.docx";
        return response()->streamDownload(function () {
            IOFactory::createWriter($this->phpWord, 'Word2007')->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    protected function renderHeader(string $tanggalLabel): void
    {
        $logoPath = public_path('images/logo-darussalam.png');
        $headerTable = $this->section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $headerTable->addRow();

        $cellLogo = $headerTable->addCell(1200, ['valign' => 'center']);
        if (file_exists($logoPath)) {
            $cellLogo->addImage($logoPath, ['width' => 55, 'height' => 55, 'alignment' => Jc::CENTER]);
        }

        $cellText = $headerTable->addCell(8800, ['valign' => 'center']);
        $cellText->addText('SMP TERPADU DARUSSALAM',
            ['bold' => true, 'size' => 15, 'color' => self::COLOR_DARK],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $cellText->addText('Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok',
            ['size' => 9, 'color' => '555555'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        $this->section->addTextBreak(0);
        $this->section->addText('LAPORAN ABSENSI HARIAN',
            ['bold' => true, 'size' => 13, 'color' => self::COLOR_PRIMARY],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 0]);
        $this->section->addText($tanggalLabel,
            ['size' => 11, 'color' => '333333'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);
    }

    protected function renderTable(iterable $data): void
    {
        $border = ['borderSize' => 6, 'borderColor' => 'BBBBBB'];
        $table = $this->section->addTable(array_merge($border, [
            'cellMargin' => 50, 'alignment' => Jc::CENTER, 'width' => 100 * 50, 'unit' => 'pct',
        ]));

        $hFont = ['bold' => true, 'color' => self::PUTIH, 'size' => 9];
        $hCell = ['bgColor' => self::COLOR_PRIMARY, 'valign' => 'center'];
        $hPara = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];

        $table->addRow(350, ['tblHeader' => true]);
        $table->addCell(self::COL_NO, $hCell)->addText('No', $hFont, $hPara);
        $table->addCell(self::COL_NAMA, $hCell)->addText('Nama Guru', $hFont, $hPara);
        $table->addCell(self::COL_JABMAP, $hCell)->addText('Jabatan / Mapel', $hFont, $hPara);
        $table->addCell(self::COL_STATUS, $hCell)->addText('Status', $hFont, $hPara);
        $table->addCell(self::COL_JAM, $hCell)->addText('Jam Masuk', $hFont, $hPara);
        $table->addCell(self::COL_METODE, $hCell)->addText('Metode', $hFont, $hPara);
        $table->addCell(self::COL_KET, $hCell)->addText('Keterangan', $hFont, $hPara);

        $bFont = ['size' => 9, 'color' => '1f2937'];
        $cPara = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];
        $lPara = ['alignment' => Jc::START, 'spaceAfter' => 0];

        $statusColors = ['hadir' => 'D1FAE5', 'izin' => 'FEF3C7', 'sakit' => 'DBEAFE', 'alpa' => 'FEE2E2'];
        $i = 0;

        foreach ($data as $row) {
            $i++;
            $table->addRow();
            $table->addCell(self::COL_NO)->addText((string)$i, $bFont, $cPara);
            $table->addCell(self::COL_NAMA)->addText($row->guru->nama_lengkap, $bFont, $lPara);
            $table->addCell(self::COL_JABMAP)->addText(
                $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-'), $bFont, $lPara);

            $stColor = $statusColors[$row->status] ?? 'F9FAFB';
            $stLabel = $row->status === 'belum' ? '-' : ucfirst($row->status);
            $table->addCell(self::COL_STATUS, ['bgColor' => $stColor])
                  ->addText($stLabel, ['size' => 9, 'bold' => true], $cPara);

            $table->addCell(self::COL_JAM)->addText($row->jam_masuk ?: '-', $bFont, $cPara);

            $metode = match ($row->input_method) {
                'scan' => 'Scan QR', 'manual' => 'Manual', default => '-',
            };
            $table->addCell(self::COL_METODE)->addText($metode, $bFont, $cPara);
            $table->addCell(self::COL_KET)->addText($row->keterangan ?: '-', $bFont, $lPara);
        }

        $this->section->addTextBreak(1);
    }

    protected function renderSignature(?Kepsek $kepsek, string $tanggalCetak): void
    {
        if (!$kepsek) return;

        $signTable = $this->section->addTable(['borderSize' => 0]);
        $signTable->addRow();
        $signTable->addCell(5500);
        $cellSign = $signTable->addCell(4500);

        $sf = ['size' => 10, 'color' => '1f2937'];
        $cp = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];

        $cellSign->addText("Depok, {$tanggalCetak}", $sf, $cp);
        $cellSign->addText("Kepala Sekolah,", $sf, $cp);
        for ($j = 0; $j < 4; $j++) $cellSign->addTextBreak();
        $cellSign->addText($kepsek->nama_lengkap,
            ['size' => 10, 'bold' => true, 'underline' => 'single'], $cp);
        if ($kepsek->nip) {
            $cellSign->addText('NIP. ' . $kepsek->nip, ['size' => 9, 'color' => '555555'], $cp);
        }
    }
}

<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Guru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Sheet 1: REKAP — matrix per guru.
 *
 * Layout:
 *   Row 1:  KOP - Nama Sekolah (merge A1:I1, bg hijau gelap)
 *   Row 2:  Alamat (merge A2:I2)
 *   Row 3:  Subtitle (merge A3:I3, bg hijau primary)
 *   Row 4:  [kosong]
 *   Row 5:  Header tabel (bg hijau primary, putih)
 *   Row 6+: Data rows
 *   Last:   Footer TOTAL (merge A:D)
 */
class LaporanRekapSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithEvents
{
    protected int $bulan;
    protected int $tahun;
    protected int $rowNumber = 0;

    public function __construct(int $bulan, int $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function title(): string
    {
        return 'Rekap';
    }

    public function collection()
    {
        return Guru::aktif()
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($guru) {
                $absensi = Absensi::where('id_guru', $guru->id_guru)
                                  ->bulan($this->bulan, $this->tahun)
                                  ->get();
                return (object) [
                    'guru'   => $guru,
                    'hadir'  => $absensi->where('status', 'hadir')->count(),
                    'izin'   => $absensi->where('status', 'izin')->count(),
                    'sakit'  => $absensi->where('status', 'sakit')->count(),
                    'alpa'   => $absensi->where('status', 'alpa')->count(),
                    'total'  => $absensi->count(),
                ];
            });
    }

    /**
     * Custom width per kolom (dalam karakter).
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 32,  // Nama Guru
            'C' => 18,  // NIP
            'D' => 28,  // Jabatan/Mapel
            'E' => 9,   // Hadir
            'F' => 9,   // Izin
            'G' => 9,   // Sakit
            'H' => 9,   // Alpa
            'I' => 11,  // Total
        ];
    }

    public function headings(): array
    {
        $namaBulan = \Carbon\Carbon::create()->month($this->bulan)->translatedFormat('F');

        return [
            ["SMP TERPADU DARUSSALAM"],
            ["Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok"],
            ["LAPORAN ABSENSI GURU — {$namaBulan} {$this->tahun}"],
            [],
            ['No', 'Nama Guru', 'NIP', 'Jabatan / Mapel', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Total'],
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $row->guru->nama_lengkap,
            $row->guru->nip ?: '-',
            $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-'),
            $row->hadir,
            $row->izin,
            $row->sakit,
            $row->alpa,
            $row->total,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ============== KOP: BARIS 1 (NAMA SEKOLAH) ==============
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'size'  => 16,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name'  => 'Calibri',
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1B5E20'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(32);

                // ============== KOP: BARIS 2 (ALAMAT) ==============
                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size'  => 9,
                        'color' => ['rgb' => 'FFFFFF'],
                        'italic'=> true,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1B5E20'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // ============== KOP: BARIS 3 (SUBJUDUL) ==============
                $sheet->mergeCells('A3:I3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'size'  => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E7D32'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(24);

                // ============== HEADER TABEL (Baris 5) ==============
                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size'  => 10,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E7D32'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'FFFFFF'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(28);

                // ============== DATA ROWS ==============
                $lastDataRow = $sheet->getHighestRow();

                if ($lastDataRow >= 6) {
                    // Border umum untuk semua data
                    $sheet->getStyle("A6:I{$lastDataRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'BBBBBB'],
                            ],
                        ],
                        'font' => ['size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Set row height untuk data
                    for ($r = 6; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(22);
                    }

                    // Center untuk kolom No + NIP + angka status (E-I)
                    $sheet->getStyle("A6:A{$lastDataRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C6:C{$lastDataRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E6:I{$lastDataRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Padding kiri untuk Nama & Jabatan
                    $sheet->getStyle("B6:B{$lastDataRow}")
                          ->getAlignment()->setIndent(1);
                    $sheet->getStyle("D6:D{$lastDataRow}")
                          ->getAlignment()->setIndent(1);

                    // Warna kolom Hadir
                    $sheet->getStyle("E6:E{$lastDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                    ]);
                    // Warna kolom Izin
                    $sheet->getStyle("F6:F{$lastDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                    ]);
                    // Warna kolom Sakit
                    $sheet->getStyle("G6:G{$lastDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                    ]);
                    // Warna kolom Alpa
                    $sheet->getStyle("H6:H{$lastDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                    ]);
                    // Warna kolom Total (gray + bold)
                    $sheet->getStyle("I6:I{$lastDataRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                        'font' => ['bold' => true],
                    ]);
                }

                // ============== FOOTER TOTAL ==============
                $totalRow = $lastDataRow + 1;

                // Hitung total
                $cells = $sheet->rangeToArray("E6:I{$lastDataRow}");
                $totals = [0, 0, 0, 0, 0]; // hadir, izin, sakit, alpa, total
                foreach ($cells as $row) {
                    foreach ($row as $idx => $val) {
                        $totals[$idx] += (int)$val;
                    }
                }

                // Merge A-D untuk label "TOTAL"
                $sheet->mergeCells("A{$totalRow}:D{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("E{$totalRow}", $totals[0]);
                $sheet->setCellValue("F{$totalRow}", $totals[1]);
                $sheet->setCellValue("G{$totalRow}", $totals[2]);
                $sheet->setCellValue("H{$totalRow}", $totals[3]);
                $sheet->setCellValue("I{$totalRow}", $totals[4]);

                // Style footer total
                $sheet->getStyle("A{$totalRow}:I{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size'  => 11,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E7D32'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'FFFFFF'],
                        ],
                    ],
                ]);
                $sheet->getStyle("A{$totalRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A{$totalRow}")
                      ->getAlignment()->setIndent(2);

                // Total grand jadi lebih gelap
                $sheet->getStyle("I{$totalRow}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1B5E20'],
                    ],
                ]);

                $sheet->getRowDimension($totalRow)->setRowHeight(28);

                // ============== PAGE SETUP UNTUK PRINT ==============
                $sheet->getPageSetup()
                      ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                      ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                      ->setFitToWidth(1)
                      ->setFitToHeight(0);

                $sheet->getPageMargins()
                      ->setTop(0.5)
                      ->setRight(0.4)
                      ->setLeft(0.4)
                      ->setBottom(0.5);

                // Freeze pane di bawah header tabel
                $sheet->freezePane('A6');
            },
        ];
    }
}

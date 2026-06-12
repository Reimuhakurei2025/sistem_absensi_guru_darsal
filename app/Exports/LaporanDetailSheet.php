<?php

namespace App\Exports;

use App\Models\Absensi;
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
 * Sheet 2: DETAIL — semua transaksi absensi pada bulan tersebut.
 *
 * Layout dengan kop sekolah di atas, header tabel hijau.
 * Memiliki conditional coloring untuk kolom Status berdasarkan jenis absensi.
 */
class LaporanDetailSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithEvents
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
        return 'Detail Transaksi';
    }

    public function collection()
    {
        return Absensi::with('guru')
            ->bulan($this->bulan, $this->tahun)
            ->orderBy('tanggal')
            ->orderBy('id_guru')
            ->get();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,    // No
            'B' => 14,   // Tanggal
            'C' => 12,   // Hari
            'D' => 30,   // Nama Guru
            'E' => 26,   // Jabatan/Mapel
            'F' => 10,   // Status
            'G' => 14,   // Jam Masuk
            'H' => 14,   // Metode
            'I' => 28,   // Keterangan
        ];
    }

    public function headings(): array
    {
        $namaBulan = \Carbon\Carbon::create()->month($this->bulan)->translatedFormat('F');

        return [
            ["SMP TERPADU DARUSSALAM"],
            ["DETAIL TRANSAKSI ABSENSI — {$namaBulan} {$this->tahun}"],
            [],
            ['No', 'Tanggal', 'Hari', 'Nama Guru', 'Jabatan / Mapel',
             'Status', 'Jam Masuk', 'Metode', 'Keterangan'],
        ];
    }

    public function map($absensi): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $absensi->tanggal->format('d-m-Y'),
            $absensi->tanggal->translatedFormat('l'),
            $absensi->guru->nama_lengkap,
            $absensi->guru->jabatan ?: ($absensi->guru->mata_pelajaran ?? '-'),
            ucfirst($absensi->status),
            $absensi->jam_masuk ?: '-',
            $absensi->input_method === 'manual' ? 'Manual' : 'Scan QR',
            $absensi->keterangan ?: '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ============== KOP ==============
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // ============== HEADER TABEL (Baris 4) ==============
                $sheet->getStyle('A4:I4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
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
                $sheet->getRowDimension(4)->setRowHeight(26);

                // ============== DATA ROWS ==============
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 5) {
                    $sheet->getStyle("A5:I{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                        'font' => ['size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Center alignment
                    $sheet->getStyle("A5:C{$lastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E5:E{$lastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F5:H{$lastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Conditional coloring untuk kolom Status (G)
                    for ($r = 5; $r <= $lastRow; $r++) {
                        $status = strtolower($sheet->getCell("F{$r}")->getValue());
                        $colorMap = [
                            'hadir' => 'D1FAE5',
                            'izin'  => 'FEF3C7',
                            'sakit' => 'DBEAFE',
                            'alpa'  => 'FEE2E2',
                        ];
                        if (isset($colorMap[$status])) {
                            $sheet->getStyle("F{$r}")->applyFromArray([
                                'fill' => [
                                    'fillType'   => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $colorMap[$status]],
                                ],
                                'font' => ['bold' => true],
                            ]);
                        }
                    }
                }

                // ============== PAGE SETUP ==============
                $sheet->getPageSetup()
                      ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                      ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                      ->setFitToWidth(1)
                      ->setFitToHeight(0);

                $sheet->freezePane('A5');
            },
        ];
    }
}

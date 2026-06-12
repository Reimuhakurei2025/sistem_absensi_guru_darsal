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

    public function title(): string { return 'Rekap'; }

    public function collection()
    {
        return Guru::aktif()->orderBy('nama_lengkap')->get()->map(function ($guru) {
            $absensi = Absensi::where('id_guru', $guru->id_guru)
                              ->bulan($this->bulan, $this->tahun)->get();
            return (object) [
                'guru'  => $guru,
                'hadir' => $absensi->where('status', 'hadir')->count(),
                'izin'  => $absensi->where('status', 'izin')->count(),
                'sakit' => $absensi->where('status', 'sakit')->count(),
                'alpa'  => $absensi->where('status', 'alpa')->count(),
                'total' => $absensi->count(),
            ];
        });
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 32, 'C' => 28, 'D' => 10, 'E' => 10, 'F' => 10, 'G' => 10, 'H' => 12];
    }

    public function headings(): array
    {
        $nb = \Carbon\Carbon::create()->month($this->bulan)->translatedFormat('F');
        return [
            ["SMP TERPADU DARUSSALAM"],
            ["Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok"],
            ["LAPORAN ABSENSI GURU — {$nb} {$this->tahun}"],
            [],
            ['No', 'Nama Guru', 'Jabatan / Mapel', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Total'],
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $row->guru->nama_lengkap,
            $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-'),
            $row->hadir, $row->izin, $row->sakit, $row->alpa, $row->total,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s = $event->sheet->getDelegate();
                $lastCol = 'H';

                // KOP row 1
                $s->mergeCells("A1:{$lastCol}1");
                $s->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $s->getRowDimension(1)->setRowHeight(28);

                // KOP row 2
                $s->mergeCells("A2:{$lastCol}2");
                $s->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => 'FFFFFF'], 'italic' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $s->getRowDimension(2)->setRowHeight(18);

                // KOP row 3
                $s->mergeCells("A3:{$lastCol}3");
                $s->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $s->getRowDimension(3)->setRowHeight(24);

                // HEADER row 5
                $s->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);
                $s->getRowDimension(5)->setRowHeight(26);

                // DATA
                $lr = $s->getHighestRow();
                if ($lr >= 6) {
                    $s->getStyle("A6:{$lastCol}{$lr}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBBBBB']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $s->getStyle("A6:A{$lr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $s->getStyle("D6:{$lastCol}{$lr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Status colors: D=Hadir, E=Izin, F=Sakit, G=Alpa, H=Total
                    $colors = ['D' => 'D1FAE5', 'E' => 'FEF3C7', 'F' => 'DBEAFE', 'G' => 'FEE2E2', 'H' => 'F3F4F6'];
                    foreach ($colors as $col => $rgb) {
                        $s->getStyle("{$col}6:{$col}{$lr}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rgb]],
                        ]);
                    }
                    $s->getStyle("H6:H{$lr}")->getFont()->setBold(true);
                }

                // FOOTER TOTAL
                $tr = $lr + 1;
                $s->mergeCells("A{$tr}:C{$tr}");
                $s->setCellValue("A{$tr}", 'TOTAL');
                // Hitung totals dari data
                $cells = $s->rangeToArray("D6:H{$lr}");
                $totals = [0, 0, 0, 0, 0];
                foreach ($cells as $row) {
                    foreach ($row as $idx => $val) { $totals[$idx] += (int)$val; }
                }
                $s->setCellValue("D{$tr}", $totals[0]);
                $s->setCellValue("E{$tr}", $totals[1]);
                $s->setCellValue("F{$tr}", $totals[2]);
                $s->setCellValue("G{$tr}", $totals[3]);
                $s->setCellValue("H{$tr}", $totals[4]);

                $s->getStyle("A{$tr}:{$lastCol}{$tr}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);
                $s->getStyle("A{$tr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setIndent(2);
                $s->getStyle("H{$tr}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                ]);
                $s->getRowDimension($tr)->setRowHeight(28);

                // PAGE SETUP
                $s->getPageSetup()
                  ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                  ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                  ->setFitToWidth(1)->setFitToHeight(0);
                $s->freezePane('A6');
            },
        ];
    }
}

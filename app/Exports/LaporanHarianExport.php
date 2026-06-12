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

class LaporanHarianExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithEvents
{
    protected string $tanggal;
    protected int $rowNumber = 0;

    public function __construct(string $tanggal)
    {
        $this->tanggal = $tanggal;
    }

    public function title(): string { return 'Harian'; }

    public function collection()
    {
        $guruAktif = Guru::aktif()->orderBy('nama_lengkap')->get();
        $absensi   = Absensi::whereDate('tanggal', $this->tanggal)->get()->keyBy('id_guru');

        return $guruAktif->map(function ($guru) use ($absensi) {
            $a = $absensi->get($guru->id_guru);
            return (object) [
                'guru'         => $guru,
                'status'       => $a?->status ?? 'Belum Absen',
                'jam_masuk'    => $a?->jam_masuk ?: '-',
                'input_method' => match ($a?->input_method) {
                    'scan'   => 'Scan QR',
                    'manual' => 'Manual',
                    default  => '-',
                },
                'keterangan' => $a?->keterangan ?: '-',
            ];
        });
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 30, 'C' => 24, 'D' => 12, 'E' => 14, 'F' => 12, 'G' => 28];
    }

    public function headings(): array
    {
        $label = \Carbon\Carbon::parse($this->tanggal)->translatedFormat('l, d F Y');
        return [
            ["SMP TERPADU DARUSSALAM"],
            ["LAPORAN ABSENSI HARIAN — {$label}"],
            [],
            ['No', 'Nama Guru', 'Jabatan / Mapel', 'Status', 'Jam Masuk', 'Metode', 'Keterangan'],
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $row->guru->nama_lengkap,
            $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-'),
            ucfirst($row->status),
            $row->jam_masuk,
            $row->input_method,
            $row->keterangan,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // KOP
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // HEADER TABEL (row 4)
                $sheet->getStyle('A4:G4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(26);

                // DATA ROWS
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 5) {
                    $sheet->getStyle("A5:G{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D5:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Conditional color per status
                    $colorMap = ['Hadir' => 'D1FAE5', 'Izin' => 'FEF3C7', 'Sakit' => 'DBEAFE', 'Alpa' => 'FEE2E2'];
                    for ($r = 5; $r <= $lastRow; $r++) {
                        $status = $sheet->getCell("D{$r}")->getValue();
                        if (isset($colorMap[$status])) {
                            $sheet->getStyle("D{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorMap[$status]]],
                                'font' => ['bold' => true],
                            ]);
                        }
                    }
                }

                // PAGE SETUP
                $sheet->getPageSetup()
                      ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                      ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                      ->setFitToWidth(1)->setFitToHeight(0);
                $sheet->freezePane('A5');
            },
        ];
    }
}

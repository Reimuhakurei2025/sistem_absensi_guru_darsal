<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Guru;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Export Excel laporan bulanan dengan 2 sheet:
 *  - Sheet 1: REKAP   → rekapitulasi per guru
 *  - Sheet 2: DETAIL  → semua transaksi absensi di bulan tersebut
 */
class LaporanBulananExport implements WithMultipleSheets
{
    use Exportable;

    protected int $bulan;
    protected int $tahun;

    public function __construct(int $bulan, int $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function sheets(): array
    {
        return [
            'Rekap'  => new LaporanRekapSheet($this->bulan, $this->tahun),
            'Detail' => new LaporanDetailSheet($this->bulan, $this->tahun),
        ];
    }
}

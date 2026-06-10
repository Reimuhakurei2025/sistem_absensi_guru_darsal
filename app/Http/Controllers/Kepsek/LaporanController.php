<?php

namespace App\Http\Controllers\Kepsek;

use App\Exports\LaporanBulananExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Services\LaporanWordGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    /**
     * Helper: kumpulkan data rekap bulanan untuk dipakai oleh
     * tampilan web, PDF, dan Excel.
     */
    private function getRekapData(int $bulan, int $tahun)
    {
        return Guru::aktif()
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($guru) use ($bulan, $tahun) {
                $absensi = Absensi::where('id_guru', $guru->id_guru)
                                  ->bulan($bulan, $tahun)
                                  ->get();

                return (object) [
                    'guru'          => $guru,
                    'jumlah_hadir'  => $absensi->where('status', 'hadir')->count(),
                    'jumlah_izin'   => $absensi->where('status', 'izin')->count(),
                    'jumlah_sakit'  => $absensi->where('status', 'sakit')->count(),
                    'jumlah_alpa'   => $absensi->where('status', 'alpa')->count(),
                    'total_absensi' => $absensi->count(),
                ];
            });
    }

    /**
     * Laporan absensi bulanan (HTML view).
     */
    public function bulanan(Request $request): View
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $gurus = $this->getRekapData($bulan, $tahun);

        return view('kepsek.laporan.bulanan', compact('gurus', 'bulan', 'tahun'));
    }

    /**
     * Export laporan bulanan ke PDF.
     */
    public function bulananPdf(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $gurus       = $this->getRekapData($bulan, $tahun);
        $kepsek      = auth('kepsek')->user();
        $namaBulan   = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
        $tanggalCetak = now()->translatedFormat('d F Y');

        $pdf = Pdf::loadView('kepsek.laporan.bulanan-pdf', compact(
            'gurus', 'bulan', 'tahun', 'kepsek', 'namaBulan', 'tanggalCetak'
        ));

        $pdf->setPaper('a4', 'landscape');

        $filename = "Laporan_Absensi_{$namaBulan}_{$tahun}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Export laporan bulanan ke Excel (xlsx).
     */
    public function bulananExcel(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $namaBulan = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
        $filename  = "Laporan_Absensi_{$namaBulan}_{$tahun}.xlsx";

        return Excel::download(new LaporanBulananExport($bulan, $tahun), $filename);
    }

    /**
     * Export laporan bulanan ke Word (docx).
     */
    public function bulananWord(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $gurus        = $this->getRekapData($bulan, $tahun);
        $kepsek       = auth('kepsek')->user();
        $namaBulan    = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
        $tanggalCetak = now()->translatedFormat('d F Y');

        $generator = new LaporanWordGenerator();
        return $generator->generate($gurus, $bulan, $tahun, $kepsek, $namaBulan, $tanggalCetak);
    }

    /**
     * Ranking kehadiran guru.
     */
    public function ranking(Request $request): View
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $ranking = Guru::aktif()
            ->withCount([
                'absensi as hadir_count' => function ($q) use ($bulan, $tahun) {
                    $q->where('status', 'hadir')
                      ->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
                },
                'absensi as izin_count' => function ($q) use ($bulan, $tahun) {
                    $q->where('status', 'izin')
                      ->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
                },
                'absensi as alpa_count' => function ($q) use ($bulan, $tahun) {
                    $q->where('status', 'alpa')
                      ->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
                },
            ])
            ->orderByDesc('hadir_count')
            ->orderBy('alpa_count')
            ->get();

        return view('kepsek.laporan.ranking', compact('ranking', 'bulan', 'tahun'));
    }
}

<?php

namespace App\Http\Controllers\Shared;

use App\Exports\LaporanHarianExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Services\LaporanHarianWordGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanHarianController extends Controller
{
    private function getCurrentRole(): string
    {
        if (Auth::guard('kepsek')->check()) return 'kepsek';
        if (Auth::guard('admin')->check()) return 'admin';
        abort(403);
    }

    private function getKepsekUser()
    {
        // Untuk tanda tangan: selalu ambil data kepsek (meski yang akses admin)
        return \App\Models\Kepsek::where('is_active', true)->first();
    }

    /**
     * Helper: ambil data absensi harian lengkap dengan timestamp.
     */
    private function getDataHarian(string $tanggal)
    {
        $guruAktif = Guru::aktif()->orderBy('nama_lengkap')->get();

        $absensiHariIni = Absensi::with('guru')
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('id_guru');

        return $guruAktif->map(function ($guru) use ($absensiHariIni) {
            $absen = $absensiHariIni->get($guru->id_guru);
            return (object) [
                'guru'         => $guru,
                'status'       => $absen?->status ?? 'belum',
                'jam_masuk'    => $absen?->jam_masuk,       // H:i:s
                'input_method' => $absen?->input_method,
                'keterangan'   => $absen?->keterangan,
            ];
        });
    }

    // ============================================================
    // WEB VIEW
    // ============================================================

    public function index(Request $request): View
    {
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $data    = $this->getDataHarian($tanggal);
        $role    = $this->getCurrentRole();

        // Hitung ringkasan
        $ringkasan = (object) [
            'hadir' => $data->where('status', 'hadir')->count(),
            'izin'  => $data->where('status', 'izin')->count(),
            'sakit' => $data->where('status', 'sakit')->count(),
            'alpa'  => $data->where('status', 'alpa')->count(),
            'belum' => $data->where('status', 'belum')->count(),
            'total' => $data->count(),
        ];

        return view('shared.laporan.harian', compact('data', 'tanggal', 'role', 'ringkasan'));
    }

    // ============================================================
    // EXPORT PDF
    // ============================================================

    public function exportPdf(Request $request)
    {
        $tanggal      = $request->get('tanggal', now()->format('Y-m-d'));
        $data         = $this->getDataHarian($tanggal);
        $kepsek       = $this->getKepsekUser();
        $tanggalLabel = \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y');
        $tanggalCetak = now()->translatedFormat('d F Y');

        $pdf = Pdf::loadView('shared.laporan.harian-pdf', compact(
            'data', 'tanggal', 'kepsek', 'tanggalLabel', 'tanggalCetak'
        ));

        $pdf->setPaper('a4', 'portrait');

        $filename = 'Laporan_Harian_' . $tanggal . '.pdf';
        return $pdf->download($filename);
    }

    // ============================================================
    // EXPORT EXCEL
    // ============================================================

    public function exportExcel(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $filename = 'Laporan_Harian_' . $tanggal . '.xlsx';

        return Excel::download(new LaporanHarianExport($tanggal), $filename);
    }

    // ============================================================
    // EXPORT WORD
    // ============================================================

    public function exportWord(Request $request)
    {
        $tanggal      = $request->get('tanggal', now()->format('Y-m-d'));
        $data         = $this->getDataHarian($tanggal);
        $kepsek       = $this->getKepsekUser();
        $tanggalLabel = \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y');
        $tanggalCetak = now()->translatedFormat('d F Y');

        $generator = new LaporanHarianWordGenerator();
        return $generator->generate($data, $tanggal, $kepsek, $tanggalLabel, $tanggalCetak);
    }
}

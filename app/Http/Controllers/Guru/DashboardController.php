<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard guru — status absensi hari ini + riwayat terakhir.
     */
    public function index(): View
    {
        $guru = Auth::guard('guru')->user();

        // Status absensi hari ini
        $absensiHariIni = Absensi::where('id_guru', $guru->id_guru)
                                 ->whereDate('tanggal', today())
                                 ->first();

        // Statistik bulan ini
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $statistikBulanIni = [
            'hadir' => Absensi::where('id_guru', $guru->id_guru)
                              ->bulan($bulanIni, $tahunIni)
                              ->where('status', 'hadir')->count(),
            'izin'  => Absensi::where('id_guru', $guru->id_guru)
                              ->bulan($bulanIni, $tahunIni)
                              ->where('status', 'izin')->count(),
            'sakit' => Absensi::where('id_guru', $guru->id_guru)
                              ->bulan($bulanIni, $tahunIni)
                              ->where('status', 'sakit')->count(),
            'alpa'  => Absensi::where('id_guru', $guru->id_guru)
                              ->bulan($bulanIni, $tahunIni)
                              ->where('status', 'alpa')->count(),
        ];

        // 5 absensi terakhir
        $riwayatTerakhir = Absensi::where('id_guru', $guru->id_guru)
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        return view('guru.dashboard', compact(
            'guru',
            'absensiHariIni',
            'statistikBulanIni',
            'riwayatTerakhir'
        ));
    }

    /**
     * Riwayat absensi guru (lengkap).
     */
    public function riwayat(Request $request): View
    {
        $guru = Auth::guard('guru')->user();

        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $riwayat = Absensi::where('id_guru', $guru->id_guru)
            ->bulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('guru.riwayat', compact('riwayat', 'bulan', 'tahun'));
    }
}

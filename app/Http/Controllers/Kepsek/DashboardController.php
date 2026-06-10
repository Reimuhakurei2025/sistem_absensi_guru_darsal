<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalGuru        = Guru::aktif()->count();
        $totalGuruNonaktif = Guru::where('is_active', false)->count();

        // Statistik hari ini
        $hadirHariIni = Absensi::whereDate('tanggal', today())
                               ->where('status', 'hadir')
                               ->count();
        $izinHariIni  = Absensi::whereDate('tanggal', today())
                               ->where('status', 'izin')
                               ->count();
        $sakitHariIni = Absensi::whereDate('tanggal', today())
                               ->where('status', 'sakit')
                               ->count();

        $belumAbsen = $totalGuru - ($hadirHariIni + $izinHariIni + $sakitHariIni);

        // 5 absensi terakhir untuk preview
        $absensiTerakhir = Absensi::with('guru')
            ->whereDate('tanggal', today())
            ->orderBy('jam_masuk', 'desc')
            ->limit(5)
            ->get();

        return view('kepsek.dashboard', compact(
            'totalGuru',
            'totalGuruNonaktif',
            'hadirHariIni',
            'izinHariIni',
            'sakitHariIni',
            'belumAbsen',
            'absensiTerakhir'
        ));
    }
}

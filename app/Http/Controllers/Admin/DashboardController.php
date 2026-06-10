<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalGuru   = Guru::aktif()->count();
        $hadirHariIni = Absensi::whereDate('tanggal', today())
                               ->where('status', 'hadir')
                               ->count();
        $belumAbsen  = $totalGuru - Absensi::whereDate('tanggal', today())->count();

        return view('admin.dashboard', compact('totalGuru', 'hadirHariIni', 'belumAbsen'));
    }
}

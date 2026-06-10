<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * AbsensiController (Guru) - LOGIKA UTAMA SISTEM ABSENSI BARCODE.
 *
 * Alur:
 *   1. Guru login
 *   2. Guru buka halaman scan → kamera aktif
 *   3. Guru scan QR Code milik sendiri (yang dicetak oleh admin)
 *   4. JS membaca token QR → kirim AJAX ke endpoint scan()
 *   5. Server validasi:
 *        a. Token ada di DB?
 *        b. Token milik guru yang sedang login? (bukan guru lain)
 *        c. Belum absen hari ini?
 *   6. Jika valid → simpan absensi → response sukses
 *
 * Validasi a, b, c WAJIB sesuai spec project.
 */
class AbsensiController extends Controller
{
    /**
     * Tampilkan halaman scan barcode.
     */
    public function scan(): View|RedirectResponse
    {
        $guru = Auth::guard('guru')->user();

        // Jika sudah absen hari ini, redirect ke dashboard
        if ($guru->sudahAbsenHariIni()) {
            return redirect()->route('guru.dashboard')
                ->with('info', 'Anda sudah melakukan absensi hari ini.');
        }

        return view('guru.scan');
    }

    /**
     * Endpoint AJAX: proses hasil scan barcode.
     *
     * Request: { "token": "GR-XXXXXXXX" }
     * Response: { "status": "success|error", "message": "..." }
     */
    public function processScan(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'max:64'],
        ]);

        $guru = Auth::guard('guru')->user();
        $scannedToken = trim($request->token);

        // ============================================================
        // VALIDASI 1: Token ada di database?
        // ============================================================
        $guruByToken = Guru::where('barcode_token', $scannedToken)
                           ->where('is_active', true)
                           ->first();

        if (!$guruByToken) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Barcode tidak valid atau guru tidak aktif.',
            ], 422);
        }

        // ============================================================
        // VALIDASI 2: Token milik guru yang sedang login?
        // (CRITICAL: cegah guru pakai barcode guru lain)
        // ============================================================
        if ($guruByToken->id_guru !== $guru->id_guru) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Barcode ini bukan milik Anda. Silakan scan barcode Anda sendiri.',
            ], 422);
        }

        // ============================================================
        // VALIDASI 3: Belum absen hari ini?
        // ============================================================
        $sudahAbsen = Absensi::where('id_guru', $guru->id_guru)
                             ->whereDate('tanggal', today())
                             ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda sudah melakukan absensi hari ini.',
            ], 422);
        }

        // ============================================================
        // SIMPAN ABSENSI
        // ============================================================
        $absensi = Absensi::create([
            'id_guru'      => $guru->id_guru,
            'tanggal'      => today(),
            'jam_masuk'    => now()->format('H:i:s'),
            'status'       => 'hadir',
            'keterangan'   => 'Absensi via scan QR Code',
            'input_method' => 'scan',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Absensi berhasil tercatat. Selamat bekerja!',
            'data'    => [
                'nama'      => $guru->nama_lengkap,
                'tanggal'   => $absensi->tanggal->format('d M Y'),
                'jam_masuk' => substr($absensi->jam_masuk, 0, 5),
            ],
        ]);
    }
}

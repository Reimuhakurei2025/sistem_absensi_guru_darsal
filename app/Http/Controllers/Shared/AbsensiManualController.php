<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * AbsensiManualController - Input absensi manual oleh Kepsek/Admin.
 *
 * Skenario penggunaan:
 *   - Guru sakit/izin tapi tidak bisa scan
 *   - Backdate untuk tanggal lalu (lupa input)
 *   - Forward date untuk izin mendatang
 *   - Bulk input saat libur sekolah atau hari khusus
 *   - Koreksi data (overwrite absensi existing)
 *
 * Akses:
 *   - Kepsek: semua fitur
 *   - Admin:  semua fitur
 *
 * Overwrite: memerlukan konfirmasi password user yang sedang login.
 */
class AbsensiManualController extends Controller
{
    /**
     * Detect role + user yang sedang login.
     * Mengembalikan: ['role' => 'kepsek'|'admin', 'user' => $userModel]
     */
    private function getCurrentActor(): array
    {
        if (Auth::guard('kepsek')->check()) {
            return ['role' => 'kepsek', 'user' => Auth::guard('kepsek')->user()];
        }
        if (Auth::guard('admin')->check()) {
            return ['role' => 'admin', 'user' => Auth::guard('admin')->user()];
        }
        abort(403, 'Tidak memiliki akses');
    }

    /**
     * Route prefix berdasarkan role aktif (untuk redirect).
     */
    private function routePrefix(): string
    {
        return $this->getCurrentActor()['role']; // 'kepsek' atau 'admin'
    }

    /**
     * AJAX endpoint: cek apakah sudah ada absensi untuk guru+tanggal.
     * Return: { exists: bool, status?: string, input_method?: string }
     */
    public function checkExisting(Request $request)
    {
        $request->validate([
            'id_guru' => ['required', 'integer'],
            'tanggal' => ['required', 'date'],
        ]);

        $existing = Absensi::where('id_guru', $request->id_guru)
                           ->whereDate('tanggal', $request->tanggal)
                           ->first();

        if (!$existing) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists'       => true,
            'status'       => $existing->status,
            'input_method' => $existing->input_method,
            'jam_masuk'    => $existing->jam_masuk
                                ? substr($existing->jam_masuk, 0, 5)
                                : null,
        ]);
    }

    // ============================================================
    // SINGLE INPUT (1 guru, 1 tanggal)
    // ============================================================

    /**
     * Halaman form input absensi single (1 guru).
     */
    public function createSingle(): View
    {
        $gurus = Guru::aktif()->orderBy('nama_lengkap')->get();
        $role  = $this->routePrefix();

        return view('shared.absensi-manual.single', compact('gurus', 'role'));
    }

    /**
     * Simpan/update absensi 1 guru.
     */
    public function storeSingle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_guru'    => ['required', 'integer', 'exists:tb_guru,id_guru'],
            'tanggal'    => ['required', 'date'],
            'status'     => ['required', 'in:hadir,izin,sakit,alpa'],
            'jam_masuk'  => ['nullable', 'date_format:H:i'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'id_guru.required' => 'Guru wajib dipilih',
            'tanggal.required' => 'Tanggal wajib diisi',
            'status.in'        => 'Status tidak valid',
        ]);

        $actor = $this->getCurrentActor();

        // Cek apakah sudah ada absensi untuk guru ini di tanggal ini
        $existing = Absensi::where('id_guru', $validated['id_guru'])
                           ->whereDate('tanggal', $validated['tanggal'])
                           ->first();

        if ($existing) {
            // Overwrite — perlu konfirmasi password
            $request->validate([
                'confirm_password' => ['required', 'string'],
            ], [
                'confirm_password.required' => 'Konfirmasi password diperlukan untuk overwrite absensi',
            ]);

            if (!Hash::check($request->confirm_password, $actor['user']->password)) {
                return back()->withInput()
                    ->with('error', 'Password Anda salah. Overwrite dibatalkan.');
            }

            $existing->update([
                'status'        => $validated['status'],
                'jam_masuk'     => $validated['jam_masuk']  ?? $existing->jam_masuk,
                'keterangan'    => $validated['keterangan'] ?? null,
                'input_method'  => 'manual',
                'input_by_role' => $actor['role'],
                'input_by_id'   => $actor['user']->getKey(),
            ]);

            $msg = 'Absensi berhasil diperbarui (overwrite).';
        } else {
            // Insert baru
            Absensi::create([
                'id_guru'       => $validated['id_guru'],
                'tanggal'       => $validated['tanggal'],
                'jam_masuk'     => $validated['jam_masuk']  ?? null,
                'status'        => $validated['status'],
                'keterangan'    => $validated['keterangan'] ?? null,
                'input_method'  => 'manual',
                'input_by_role' => $actor['role'],
                'input_by_id'   => $actor['user']->getKey(),
            ]);

            $msg = 'Absensi berhasil dicatat.';
        }

        return redirect()
            ->route($this->routePrefix() . '.absensi-manual.single')
            ->with('success', $msg);
    }

    // ============================================================
    // BULK INPUT (banyak guru, 1 tanggal)
    // ============================================================

    /**
     * Halaman form bulk input.
     */
    public function createBulk(): View
    {
        $gurus = Guru::aktif()->orderBy('nama_lengkap')->get();
        $role  = $this->routePrefix();

        return view('shared.absensi-manual.bulk', compact('gurus', 'role'));
    }

    /**
     * Simpan bulk — array data per guru.
     *
     * Input: {
     *   tanggal: '2026-05-06',
     *   entries: [
     *     { id_guru: 1, status: 'hadir' },
     *     { id_guru: 2, status: 'sakit', keterangan: 'demam' },
     *     ...
     *   ]
     * }
     */
    public function storeBulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal'             => ['required', 'date'],
            'entries'             => ['required', 'array', 'min:1'],
            'entries.*.id_guru'   => ['required', 'integer', 'exists:tb_guru,id_guru'],
            'entries.*.status'    => ['required', 'in:hadir,izin,sakit,alpa,skip'],
            'entries.*.keterangan'=> ['nullable', 'string', 'max:500'],
        ]);

        $actor = $this->getCurrentActor();

        // Cek apakah ada absensi yang akan di-overwrite
        $guruIds = collect($validated['entries'])
            ->filter(fn($e) => ($e['status'] ?? 'skip') !== 'skip')
            ->pluck('id_guru')->toArray();

        $existingCount = Absensi::whereIn('id_guru', $guruIds)
            ->whereDate('tanggal', $validated['tanggal'])
            ->count();

        if ($existingCount > 0) {
            // Akan overwrite — wajib konfirmasi password
            $request->validate([
                'confirm_password' => ['required', 'string'],
            ], [
                'confirm_password.required' =>
                    "Ada {$existingCount} absensi yang akan di-overwrite. Konfirmasi password diperlukan.",
            ]);

            if (!Hash::check($request->confirm_password, $actor['user']->password)) {
                return back()->withInput()
                    ->with('error', 'Password Anda salah. Bulk input dibatalkan.');
            }
        }

        // Eksekusi dalam transaction agar atomic
        DB::transaction(function () use ($validated, $actor) {
            foreach ($validated['entries'] as $entry) {
                // Skip = tidak input apa-apa untuk guru ini
                if (($entry['status'] ?? 'skip') === 'skip') continue;

                Absensi::updateOrCreate(
                    [
                        'id_guru' => $entry['id_guru'],
                        'tanggal' => $validated['tanggal'],
                    ],
                    [
                        'status'        => $entry['status'],
                        'keterangan'    => $entry['keterangan'] ?? null,
                        'jam_masuk'     => $entry['status'] === 'hadir' ? now()->format('H:i:s') : null,
                        'input_method'  => 'manual',
                        'input_by_role' => $actor['role'],
                        'input_by_id'   => $actor['user']->getKey(),
                    ]
                );
            }
        });

        $totalInserted = collect($validated['entries'])
            ->filter(fn($e) => ($e['status'] ?? 'skip') !== 'skip')
            ->count();

        return redirect()
            ->route($this->routePrefix() . '.absensi-manual.bulk')
            ->with('success', "Berhasil menyimpan absensi untuk {$totalInserted} guru.");
    }

    // ============================================================
    // RIWAYAT INPUT MANUAL (audit log untuk Kepsek)
    // ============================================================

    /**
     * Tampilkan riwayat absensi yang diinput manual.
     */
    public function riwayatManual(Request $request): View
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $absensi = Absensi::with('guru')
            ->where('input_method', 'manual')
            ->bulan($bulan, $tahun)
            ->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->withQueryString();

        $role = $this->routePrefix();

        return view('shared.absensi-manual.riwayat', compact('absensi', 'bulan', 'tahun', 'role'));
    }
}

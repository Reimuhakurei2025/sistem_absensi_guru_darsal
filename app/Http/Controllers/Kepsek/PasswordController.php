<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kepsek;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * PasswordController - Reset password untuk semua user oleh Kepsek.
 *
 * Kepsek bisa reset password:
 *   - Guru (semua)
 *   - Admin (semua)
 *   - Kepsek lain (jika ada lebih dari 1)
 *
 * Setiap aksi reset memerlukan konfirmasi password Kepsek yang sedang login.
 */
class PasswordController extends Controller
{
    /**
     * Halaman reset password — list semua user dari 3 tabel.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'guru'); // default tab = guru

        $users = collect();
        switch ($tab) {
            case 'admin':
                $users = Admin::orderBy('nama_lengkap')->get()
                    ->map(fn($u) => (object)[
                        'id'           => $u->id_admin,
                        'nama_lengkap' => $u->nama_lengkap,
                        'username'     => $u->username,
                        'role'         => 'admin',
                        'is_active'    => $u->is_active,
                    ]);
                break;

            case 'kepsek':
                $users = Kepsek::orderBy('nama_lengkap')->get()
                    ->map(fn($u) => (object)[
                        'id'           => $u->id_kepsek,
                        'nama_lengkap' => $u->nama_lengkap,
                        'username'     => $u->username,
                        'role'         => 'kepsek',
                        'is_active'    => $u->is_active,
                    ]);
                break;

            case 'guru':
            default:
                $users = Guru::orderBy('nama_lengkap')->get()
                    ->map(fn($u) => (object)[
                        'id'           => $u->id_guru,
                        'nama_lengkap' => $u->nama_lengkap,
                        'username'     => $u->username,
                        'role'         => 'guru',
                        'is_active'    => $u->is_active,
                    ]);
                break;
        }

        return view('kepsek.password.index', compact('users', 'tab'));
    }

    /**
     * Eksekusi reset password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role'             => ['required', 'in:guru,admin,kepsek'],
            'user_id'          => ['required', 'integer'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
            'kepsek_password'  => ['required', 'string'],
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Verifikasi password Kepsek
        if (!Hash::check($validated['kepsek_password'], Auth::guard('kepsek')->user()->password)) {
            return back()->with('error', 'Password Kepala Sekolah salah.');
        }

        // Cari user berdasarkan role + id
        $user = match ($validated['role']) {
            'guru'   => Guru::find($validated['user_id']),
            'admin'  => Admin::find($validated['user_id']),
            'kepsek' => Kepsek::find($validated['user_id']),
        };

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $user->update(['password' => $validated['new_password']]); // auto-hashed via $casts

        return redirect()->route('kepsek.password.index', ['tab' => $validated['role']])
            ->with('success', "Password '{$user->nama_lengkap}' berhasil di-reset.");
    }
}

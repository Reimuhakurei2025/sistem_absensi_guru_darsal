<?php

namespace App\Http\Controllers\Auth;

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
 * AuthController - Menangani login & logout untuk 3 role.
 *
 * Strategi login:
 *  - User memasukkan username + password di SATU form login
 *  - Sistem memeriksa berurutan: Kepsek → Admin → Guru
 *  - Setelah berhasil login, redirect ke dashboard sesuai role
 */
class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login.
     * Memeriksa 3 tabel: Kepsek, Admin, Guru.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
            'password.min'      => 'Password minimal 6 karakter',
        ]);

        // ========================================================
        // 1) Coba login sebagai KEPSEK
        // ========================================================
        $kepsek = Kepsek::where('username', $credentials['username'])
                        ->where('is_active', true)
                        ->first();

        if ($kepsek && Hash::check($credentials['password'], $kepsek->password)) {
            Auth::guard('kepsek')->login($kepsek, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->route('kepsek.dashboard')
                ->with('success', 'Selamat datang, ' . $kepsek->nama_lengkap);
        }

        // ========================================================
        // 2) Coba login sebagai ADMIN
        // ========================================================
        $admin = Admin::where('username', $credentials['username'])
                      ->where('is_active', true)
                      ->first();

        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            Auth::guard('admin')->login($admin, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard')
                ->with('success', 'Selamat datang, ' . $admin->nama_lengkap);
        }

        // ========================================================
        // 3) Coba login sebagai GURU
        // ========================================================
        $guru = Guru::where('username', $credentials['username'])
                    ->where('is_active', true)
                    ->first();

        if ($guru && Hash::check($credentials['password'], $guru->password)) {
            Auth::guard('guru')->login($guru, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->route('guru.dashboard')
                ->with('success', 'Selamat datang, ' . $guru->nama_lengkap);
        }

        // ========================================================
        // GAGAL — semua tabel tidak match
        // ========================================================
        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah, atau akun Anda tidak aktif.');
    }

    /**
     * Logout dari guard mana pun yang sedang login.
     */
    public function logout(Request $request): RedirectResponse
    {
        foreach (['kepsek', 'admin', 'guru'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar.');
    }
}

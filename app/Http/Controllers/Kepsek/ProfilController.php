<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * ProfilController - Edit biodata Kepala Sekolah.
 *
 * Skenario penggunaan:
 *  - Update NIP Kepsek (mis. NIP final setelah SK keluar)
 *  - Update foto profil
 *  - Skenario pergantian Kepsek: edit nama+NIP saat ada Kepsek baru
 *
 * Setiap perubahan WAJIB konfirmasi password (sesuai pattern aksi sensitif).
 */
class ProfilController extends Controller
{
    /**
     * Tampilkan halaman profil + form edit.
     */
    public function edit(): View
    {
        $kepsek = Auth::guard('kepsek')->user();
        return view('kepsek.profil.edit', compact('kepsek'));
    }

    /**
     * Update data profil (biodata, NIP, foto).
     */
    public function update(Request $request): RedirectResponse
    {
        $kepsek = Auth::guard('kepsek')->user();

        $validated = $request->validate([
            'nama_lengkap'    => ['required', 'string', 'max:100'],
            'nip'             => ['nullable', 'string', 'max:20', "unique:tb_kepsek,nip,{$kepsek->id_kepsek},id_kepsek"],
            'username'        => ['required', 'string', 'max:50', 'alpha_dash', "unique:tb_kepsek,username,{$kepsek->id_kepsek},id_kepsek"],
            'email'           => ['nullable', 'email', 'max:100', "unique:tb_kepsek,email,{$kepsek->id_kepsek},id_kepsek"],
            'no_hp'           => ['nullable', 'string', 'max:15'],
            'foto'            => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'confirm_password'=> ['required', 'string'],
        ], [
            'nama_lengkap.required'    => 'Nama wajib diisi',
            'username.required'        => 'Username wajib diisi',
            'username.alpha_dash'      => 'Username hanya boleh huruf, angka, dash, dan underscore',
            'username.unique'          => 'Username sudah dipakai user lain',
            'email.email'              => 'Format email tidak valid',
            'confirm_password.required'=> 'Konfirmasi password Anda diperlukan',
            'foto.max'                 => 'Ukuran foto maksimal 2MB',
        ]);

        // Verifikasi password
        if (!Hash::check($validated['confirm_password'], $kepsek->password)) {
            return back()->withInput()
                ->with('error', 'Password Anda salah. Perubahan dibatalkan.');
        }

        // Upload foto baru jika ada
        if ($request->hasFile('foto')) {
            // Hapus foto lama kalau ada
            if ($kepsek->foto && Storage::disk('public')->exists($kepsek->foto)) {
                Storage::disk('public')->delete($kepsek->foto);
            }
            $validated['foto'] = $request->file('foto')->store('foto-kepsek', 'public');
        }

        // Hapus field confirm_password karena bukan kolom database
        unset($validated['confirm_password']);

        $kepsek->update($validated);

        return redirect()->route('kepsek.profil.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Form ganti password (terpisah dari edit profil biasa).
     */
    public function changePasswordForm(): View
    {
        return view('kepsek.profil.password');
    }

    /**
     * Proses ganti password sendiri.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $kepsek = Auth::guard('kepsek')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'new_password.required'     => 'Password baru wajib diisi',
            'new_password.min'          => 'Password baru minimal 6 karakter',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok',
        ]);

        // Cek password lama benar
        if (!Hash::check($validated['current_password'], $kepsek->password)) {
            return back()->with('error', 'Password lama Anda salah.');
        }

        // Cek password baru tidak sama dengan password lama
        if (Hash::check($validated['new_password'], $kepsek->password)) {
            return back()->with('error', 'Password baru tidak boleh sama dengan password lama.');
        }

        $kepsek->update(['password' => $validated['new_password']]);

        return redirect()->route('kepsek.profil.edit')
            ->with('success', 'Password berhasil diganti. Gunakan password baru di login berikutnya.');
    }
}

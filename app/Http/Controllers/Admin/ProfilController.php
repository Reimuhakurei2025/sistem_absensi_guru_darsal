<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * ProfilController (Admin) - Edit biodata Admin/TU.
 *
 * Setiap perubahan WAJIB konfirmasi password.
 */
class ProfilController extends Controller
{
    public function edit(): View
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profil.edit', compact('admin'));
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'nama_lengkap'    => ['required', 'string', 'max:100'],
            'username'        => ['required', 'string', 'max:50', 'alpha_dash', "unique:tb_admin,username,{$admin->id_admin},id_admin"],
            'email'           => ['nullable', 'email', 'max:100', "unique:tb_admin,email,{$admin->id_admin},id_admin"],
            'no_hp'           => ['nullable', 'string', 'max:15'],
            'foto'            => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'confirm_password'=> ['required', 'string'],
        ], [
            'nama_lengkap.required'    => 'Nama wajib diisi',
            'username.required'        => 'Username wajib diisi',
            'username.alpha_dash'      => 'Username hanya boleh huruf, angka, dash, dan underscore',
            'username.unique'          => 'Username sudah dipakai user lain',
            'confirm_password.required'=> 'Konfirmasi password Anda diperlukan',
        ]);

        if (!Hash::check($validated['confirm_password'], $admin->password)) {
            return back()->withInput()
                ->with('error', 'Password Anda salah. Perubahan dibatalkan.');
        }

        if ($request->hasFile('foto')) {
            if ($admin->foto && Storage::disk('public')->exists($admin->foto)) {
                Storage::disk('public')->delete($admin->foto);
            }
            $validated['foto'] = $request->file('foto')->store('foto-admin', 'public');
        }

        unset($validated['confirm_password']);
        $admin->update($validated);

        return redirect()->route('admin.profil.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePasswordForm(): View
    {
        return view('admin.profil.password');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'new_password.required'     => 'Password baru wajib diisi',
            'new_password.min'          => 'Password baru minimal 6 karakter',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok',
        ]);

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return back()->with('error', 'Password lama Anda salah.');
        }

        if (Hash::check($validated['new_password'], $admin->password)) {
            return back()->with('error', 'Password baru tidak boleh sama dengan password lama.');
        }

        $admin->update(['password' => $validated['new_password']]);

        return redirect()->route('admin.profil.edit')
            ->with('success', 'Password berhasil diganti.');
    }
}

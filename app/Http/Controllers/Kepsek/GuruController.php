<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * GuruController (Kepsek) - Manajemen data guru.
 *
 * Aksi sensitif yang memerlukan konfirmasi password Kepsek:
 *   - store     (tambah guru baru)
 *   - destroy   (deactivate guru → soft delete via is_active=false)
 *   - activate  (aktifkan kembali)
 *   - resetPassword
 */
class GuruController extends Controller
{
    /**
     * Daftar semua guru (aktif & nonaktif).
     */
    public function index(Request $request): View
    {
        $query = Guru::query();

        // Filter by status (aktif/nonaktif/all)
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        // Search by nama / NIP / mata pelajaran
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('nama_lengkap', 'like', "%{$q}%")
                   ->orWhere('nip', 'like', "%{$q}%")
                   ->orWhere('mata_pelajaran', 'like', "%{$q}%");
            });
        }

        $gurus = $query->orderBy('nama_lengkap')->paginate(15)->withQueryString();

        return view('kepsek.guru.index', compact('gurus'));
    }

    /**
     * Form tambah guru.
     */
    public function create(): View
    {
        return view('kepsek.guru.create');
    }

    /**
     * Simpan guru baru — perlu konfirmasi password Kepsek.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nip'              => ['nullable', 'string', 'max:20', 'unique:tb_guru,nip'],
            'nama_lengkap'     => ['required', 'string', 'max:100'],
            'jenis_kelamin'    => ['required', 'in:L,P'],
            'tempat_lahir'     => ['nullable', 'string', 'max:50'],
            'tgl_lahir'        => ['nullable', 'date'],
            'agama'            => ['nullable', 'string', 'max:20'],
            'alamat'           => ['nullable', 'string'],
            'no_hp'            => ['nullable', 'string', 'max:15'],
            'email'            => ['nullable', 'email', 'max:100', 'unique:tb_guru,email'],
            'mata_pelajaran'   => ['nullable', 'string', 'max:100'],
            'jabatan'          => ['nullable', 'string', 'max:100'],
            'username'         => ['required', 'string', 'max:50', 'alpha_dash', 'unique:tb_guru,username'],
            'password'         => ['required', 'string', 'min:6'],
            'foto'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'kepsek_password'  => ['required', 'string'],
        ], [
            'kepsek_password.required' => 'Konfirmasi password Kepala Sekolah wajib diisi',
            'username.alpha_dash'      => 'Username hanya boleh huruf, angka, dash, dan underscore',
        ]);

        // Verifikasi password Kepsek
        if (!Hash::check($validated['kepsek_password'], Auth::guard('kepsek')->user()->password)) {
            return back()->withInput()->with('error', 'Password Kepala Sekolah salah.');
        }

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto-guru', 'public');
        }

        Guru::create([
            'nip'            => $validated['nip']            ?? null,
            'nama_lengkap'   => $validated['nama_lengkap'],
            'jenis_kelamin'  => $validated['jenis_kelamin'],
            'tempat_lahir'   => $validated['tempat_lahir']   ?? null,
            'tgl_lahir'      => $validated['tgl_lahir']      ?? null,
            'agama'          => $validated['agama']          ?? null,
            'alamat'         => $validated['alamat']         ?? null,
            'no_hp'          => $validated['no_hp']          ?? null,
            'email'          => $validated['email']          ?? null,
            'mata_pelajaran' => $validated['mata_pelajaran'] ?? null,
            'jabatan'        => $validated['jabatan']        ?? null,
            'username'       => $validated['username'],
            'password'       => $validated['password'],   // auto-hashed via $casts
            'foto'           => $fotoPath,
            'barcode_token'  => Guru::generateBarcodeToken(),
            'is_active'      => true,
        ]);

        return redirect()->route('kepsek.guru.index')
            ->with('success', "Guru '{$validated['nama_lengkap']}' berhasil ditambahkan.");
    }

    /**
     * Detail guru.
     */
    public function show(Guru $guru): View
    {
        return view('kepsek.guru.show', compact('guru'));
    }

    /**
     * Form edit guru.
     */
    public function edit(Guru $guru): View
    {
        return view('kepsek.guru.edit', compact('guru'));
    }

    /**
     * Update data guru (tanpa password — password reset terpisah).
     */
    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $validated = $request->validate([
            'nip'            => ['nullable', 'string', 'max:20', "unique:tb_guru,nip,{$guru->id_guru},id_guru"],
            'nama_lengkap'   => ['required', 'string', 'max:100'],
            'jenis_kelamin'  => ['required', 'in:L,P'],
            'tempat_lahir'   => ['nullable', 'string', 'max:50'],
            'tgl_lahir'      => ['nullable', 'date'],
            'agama'          => ['nullable', 'string', 'max:20'],
            'alamat'         => ['nullable', 'string'],
            'no_hp'          => ['nullable', 'string', 'max:15'],
            'email'          => ['nullable', 'email', 'max:100', "unique:tb_guru,email,{$guru->id_guru},id_guru"],
            'mata_pelajaran' => ['nullable', 'string', 'max:100'],
            'jabatan'        => ['nullable', 'string', 'max:100'],
            'username'       => ['required', 'string', 'max:50', 'alpha_dash', "unique:tb_guru,username,{$guru->id_guru},id_guru"],
            'foto'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore',
            'username.unique'     => 'Username sudah dipakai guru lain',
        ]);

        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
                Storage::disk('public')->delete($guru->foto);
            }
            $validated['foto'] = $request->file('foto')->store('foto-guru', 'public');
        }

        $guru->update($validated);

        return redirect()->route('kepsek.guru.index')
            ->with('success', "Data guru '{$guru->nama_lengkap}' berhasil diperbarui.");
    }

    /**
     * Deactivate guru (soft delete via is_active=false).
     * Memerlukan konfirmasi password Kepsek.
     */
    public function deactivate(Request $request, Guru $guru): RedirectResponse
    {
        $request->validate([
            'kepsek_password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->kepsek_password, Auth::guard('kepsek')->user()->password)) {
            return back()->with('error', 'Password Kepala Sekolah salah.');
        }

        $guru->update(['is_active' => false]);

        return redirect()->route('kepsek.guru.index')
            ->with('success', "Guru '{$guru->nama_lengkap}' berhasil dinonaktifkan.");
    }

    /**
     * Aktifkan kembali guru yang sebelumnya dinonaktifkan.
     */
    public function activate(Request $request, Guru $guru): RedirectResponse
    {
        $request->validate([
            'kepsek_password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->kepsek_password, Auth::guard('kepsek')->user()->password)) {
            return back()->with('error', 'Password Kepala Sekolah salah.');
        }

        $guru->update(['is_active' => true]);

        return redirect()->route('kepsek.guru.index')
            ->with('success', "Guru '{$guru->nama_lengkap}' berhasil diaktifkan kembali.");
    }
}

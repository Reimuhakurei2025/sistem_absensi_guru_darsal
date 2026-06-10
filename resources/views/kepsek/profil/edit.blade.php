@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
    <div class="max-w-3xl">

        {{-- Tab navigasi --}}
        <div class="flex gap-1 mb-4 bg-white p-1 rounded-lg shadow-card">
            <a href="{{ route('kepsek.profil.edit') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium
                      bg-primary-700 text-white">
                <x-sidebar-icon name="user" class="w-4 h-4" />
                Biodata
            </a>
            <a href="{{ route('kepsek.profil.password') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium
                      text-gray-600 hover:bg-gray-100">
                <x-sidebar-icon name="key" class="w-4 h-4" />
                Ganti Password
            </a>
        </div>

        {{-- Info penting tentang data ini --}}
        <div class="card bg-primary-50 border-primary-200 mb-4 text-sm text-primary-900">
            <div class="flex items-start gap-2">
                <x-sidebar-icon name="document" class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <div>
                    <strong class="font-semibold">Catatan penting:</strong>
                    <p class="mt-1 text-xs">
                        Data <strong>Nama Lengkap</strong> dan <strong>NIP</strong> di sini akan otomatis tercetak
                        di <strong>laporan absensi bulanan</strong> sebagai tanda tangan Kepala Sekolah.
                        Update data ini jika ada pergantian Kepala Sekolah.
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Edit Profil --}}
        <form method="POST" action="{{ route('kepsek.profil.update') }}"
              enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Foto Profil --}}
            <div class="card">
                <h3 class="text-base font-semibold text-gray-800 mb-3">Foto Profil</h3>

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    {{-- Preview foto --}}
                    <div class="flex-shrink-0">
                        @if($kepsek->foto)
                            <img id="foto-preview"
                                 src="{{ asset('storage/' . $kepsek->foto) }}"
                                 class="w-24 h-24 rounded-full object-cover border-4 border-primary-100"
                                 alt="Foto Profil">
                        @else
                            <div id="foto-placeholder"
                                 class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center
                                        text-primary-700 text-3xl font-semibold border-4 border-primary-100">
                                {{ strtoupper(substr($kepsek->nama_lengkap, 0, 1)) }}
                            </div>
                            <img id="foto-preview" src="" class="w-24 h-24 rounded-full object-cover border-4 border-primary-100 hidden" alt="">
                        @endif
                    </div>

                    {{-- Input file --}}
                    <div class="flex-1 w-full">
                        <input type="file" name="foto" id="input-foto" accept="image/*"
                               class="block w-full text-sm text-gray-600
                                      file:mr-3 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:bg-primary-50 file:text-primary-700
                                      file:font-medium hover:file:bg-primary-100"
                               onchange="previewFoto(event)">
                        <p class="text-xs text-gray-400 mt-2">
                            JPG/PNG, maksimal 2MB. Kosongkan jika tidak ingin mengganti foto.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Data Biodata --}}
            <div class="card">
                <h3 class="text-base font-semibold text-gray-800 mb-3">Biodata</h3>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap"
                               value="{{ old('nama_lengkap', $kepsek->nama_lengkap) }}"
                               class="form-input" required>
                        <p class="text-xs text-gray-400 mt-1">
                            Akan tercetak di laporan absensi sebagai tanda tangan.
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" name="nip"
                               value="{{ old('nip', $kepsek->nip) }}"
                               class="form-input font-mono"
                               placeholder="Contoh: 197001012000011001">
                        <p class="text-xs text-gray-400 mt-1">
                            Akan tercetak di bawah nama Kepala Sekolah pada laporan.
                        </p>
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $kepsek->email) }}"
                               class="form-input" placeholder="email@example.com">
                        <p class="text-xs text-gray-400 mt-1">Opsional.</p>
                    </div>

                    <div>
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp"
                               value="{{ old('no_hp', $kepsek->no_hp) }}"
                               class="form-input" placeholder="08xxxxxxxxxx">
                    </div>

                    {{-- Username (editable, dengan warning) --}}
                    <div class="sm:col-span-2">
                        <label class="form-label">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username"
                               value="{{ old('username', $kepsek->username) }}"
                               class="form-input font-mono"
                               pattern="[a-zA-Z0-9_\-\.]+" required>
                        <p class="text-xs text-amber-600 mt-1">
                            ⚠️ Jika diubah, Anda harus login ulang dengan username baru.
                            Hanya boleh huruf, angka, titik (.), dash (-), dan underscore (_).
                        </p>
                    </div>
                </div>
            </div>

            {{-- Konfirmasi Password --}}
            <div class="card border-yellow-200 bg-yellow-50">
                <h3 class="text-base font-semibold text-yellow-800 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Konfirmasi Password
                </h3>
                <p class="text-sm text-yellow-700 mb-3">
                    Untuk keamanan, masukkan password Anda saat ini untuk menyimpan perubahan.
                </p>

                <input type="password" name="confirm_password" class="form-input"
                       placeholder="Password Anda saat ini" required autocomplete="current-password">
            </div>

            {{-- Submit --}}
            <div class="flex gap-2 sticky bottom-4">
                <a href="{{ route('kepsek.dashboard') }}" class="btn-secondary flex-1 sm:flex-none">
                    Batal
                </a>
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
function previewFoto(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
        const preview = document.getElementById('foto-preview');
        const placeholder = document.getElementById('foto-placeholder');
        preview.src = ev.target.result;
        preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
}
</script>
@endpush

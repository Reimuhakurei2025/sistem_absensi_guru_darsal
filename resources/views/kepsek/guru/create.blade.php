@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Tambah Guru')
@section('page-title', 'Tambah Guru Baru')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('kepsek.guru.index') }}"
           class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-700 mb-4">
            ← Kembali ke daftar
        </a>

        <form id="form-tambah-guru"
              method="POST"
              action="{{ route('kepsek.guru.store') }}"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf

            {{-- ===== DATA AKUN ===== --}}
            <div class="card">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Data Akun</h3>
                <p class="text-sm text-gray-500 mb-4">Username dan password untuk login.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" value="{{ old('username') }}"
                               class="form-input @error('username') border-red-400 @enderror"
                               placeholder="contoh: ahmad.fauzi" required>
                        @error('username') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password"
                               class="form-input @error('password') border-red-400 @enderror"
                               placeholder="Minimal 6 karakter" required minlength="6">
                        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ===== DATA PRIBADI ===== --}}
            <div class="card">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Data Pribadi</h3>
                <p class="text-sm text-gray-500 mb-4">Identitas guru.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                               class="form-input" placeholder="Nama beserta gelar" required>
                    </div>
                    <div>
                        <label class="form-label">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip') }}"
                               class="form-input font-mono" placeholder="Kosongkan jika tidak ada">
                        <p class="text-xs text-gray-400 mt-1">Opsional (sekolah swasta).</p>
                    </div>
                    <div>
                        <label class="form-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" class="form-input" required>
                            <option value="">Pilih...</option>
                            <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                               class="form-input" placeholder="Kota lahir">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Agama</label>
                        <select name="agama" class="form-input">
                            <option value="">Pilih...</option>
                            @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $a)
                                <option value="{{ $a }}" {{ old('agama') === $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                               class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" rows="2" class="form-input"
                                  placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ===== DATA KEPEGAWAIAN ===== --}}
            <div class="card">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Data Kepegawaian</h3>
                <p class="text-sm text-gray-500 mb-4">Informasi terkait pekerjaan.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                               class="form-input"
                               placeholder="contoh: Guru, Walas IX.A, W.Kurikulum">
                    </div>
                    <div>
                        <label class="form-label">Mata Pelajaran</label>
                        <input type="text" name="mata_pelajaran" value="{{ old('mata_pelajaran') }}"
                               class="form-input" placeholder="contoh: Matematika">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-input" placeholder="Kosongkan jika tidak ada">
                        <p class="text-xs text-gray-400 mt-1">Opsional.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Foto (opsional)</label>
                        <input type="file" name="foto" accept="image/*"
                               class="block w-full text-sm text-gray-600
                                      file:mr-3 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:bg-primary-50 file:text-primary-700
                                      file:font-medium hover:file:bg-primary-100">
                        <p class="text-xs text-gray-400 mt-1">JPG/PNG, maks. 2MB</p>
                    </div>
                </div>
            </div>

            {{-- Info: barcode auto-generate --}}
            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 text-sm text-primary-800">
                <div class="flex gap-2">
                    <x-sidebar-icon name="qrcode" class="w-5 h-5 flex-shrink-0 mt-0.5" />
                    <div>
                        <strong class="font-semibold">Barcode otomatis dibuat.</strong>
                        Setelah guru disimpan, sistem akan generate QR Code unik untuk absensi.
                        Anda dapat mencetaknya dari menu Admin.
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex gap-2 sticky bottom-4">
                <a href="{{ route('kepsek.guru.index') }}" class="btn-secondary flex-1 sm:flex-none">
                    Batal
                </a>
                <button type="button"
                        onclick="confirmTambahGuru()"
                        class="btn-primary flex-1 sm:flex-none">
                    Simpan Guru
                </button>
            </div>
        </form>
    </div>

    <x-password-confirm-modal />
@endsection

@push('scripts')
<script>
function confirmTambahGuru() {
    const namaInput = document.querySelector('input[name="nama_lengkap"]');
    const nama = namaInput?.value.trim() || 'guru baru';

    document.getElementById('password-confirm-modal-title').textContent = 'Tambah Guru?';
    document.getElementById('password-confirm-modal-message').textContent =
        `Anda akan menambahkan "${nama}" sebagai guru baru. Masukkan password Anda untuk konfirmasi.`;

    openPasswordModal('form-tambah-guru');
}
</script>
@endpush

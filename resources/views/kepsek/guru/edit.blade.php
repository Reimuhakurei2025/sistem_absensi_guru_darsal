@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Edit Guru')
@section('page-title', 'Edit Data Guru')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('kepsek.guru.index') }}"
           class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-700 mb-4">
            ← Kembali ke daftar
        </a>

        <form method="POST"
              action="{{ route('kepsek.guru.update', $guru) }}"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Header info --}}
            <div class="card flex items-center gap-3">
                @if($guru->foto)
                    <img src="{{ asset('storage/' . $guru->foto) }}"
                         class="w-12 h-12 rounded-full object-cover" alt="Foto">
                @else
                    <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center
                                text-primary-700 font-semibold">
                        {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">{{ $guru->nama_lengkap }}</div>
                    <div class="text-xs text-gray-500">
                        Username: <span class="font-mono">{{ $guru->username }}</span> ·
                        Status: {{ $guru->is_active ? 'Aktif' : 'Nonaktif' }}
                    </div>
                </div>
            </div>

            {{-- Form fields --}}
            <div class="card">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Data Pribadi</h3>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap"
                               value="{{ old('nama_lengkap', $guru->nama_lengkap) }}"
                               class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">NIP</label>
                        <input type="text" name="nip"
                               value="{{ old('nip', $guru->nip) }}"
                               class="form-input font-mono"
                               placeholder="Kosongkan jika tidak ada">
                    </div>
                    <div>
                        <label class="form-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" class="form-input" required>
                            <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir"
                               value="{{ old('tempat_lahir', $guru->tempat_lahir) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir"
                               value="{{ old('tgl_lahir', $guru->tgl_lahir?->format('Y-m-d')) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Agama</label>
                        <select name="agama" class="form-input">
                            <option value="">Pilih...</option>
                            @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $a)
                                <option value="{{ $a }}"
                                    {{ old('agama', $guru->agama) === $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp"
                               value="{{ old('no_hp', $guru->no_hp) }}" class="form-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" rows="2" class="form-input">{{ old('alamat', $guru->alamat) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Data Kepegawaian</h3>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan"
                               value="{{ old('jabatan', $guru->jabatan) }}"
                               class="form-input"
                               placeholder="contoh: Guru, Walas IX.A, W.Kurikulum">
                    </div>
                    <div>
                        <label class="form-label">Mata Pelajaran</label>
                        <input type="text" name="mata_pelajaran"
                               value="{{ old('mata_pelajaran', $guru->mata_pelajaran) }}" class="form-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $guru->email) }}"
                               class="form-input"
                               placeholder="Kosongkan jika tidak ada">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username"
                               value="{{ old('username', $guru->username) }}"
                               class="form-input font-mono"
                               pattern="[a-zA-Z0-9_\-\.]+" required>
                        <p class="text-xs text-amber-600 mt-1">
                            ⚠️ Jika diubah, guru harus login dengan username baru.
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Ganti Foto (opsional)</label>
                        <input type="file" name="foto" accept="image/*"
                               class="block w-full text-sm text-gray-600
                                      file:mr-3 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:bg-primary-50 file:text-primary-700
                                      file:font-medium hover:file:bg-primary-100">
                        <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak diubah. JPG/PNG, maks. 2MB</p>
                    </div>
                </div>
            </div>

            {{-- Info barcode --}}
            <div class="card bg-gray-50">
                <div class="flex items-center gap-3">
                    <x-sidebar-icon name="qrcode" class="w-5 h-5 text-gray-600" />
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-700">Token Barcode</div>
                        <div class="text-xs text-gray-500 font-mono">{{ $guru->barcode_token }}</div>
                    </div>
                    <a href="{{ route('admin.guru.barcode', $guru) }}" target="_blank"
                       class="btn-secondary text-xs">Lihat QR</a>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('kepsek.guru.index') }}" class="btn-secondary flex-1 sm:flex-none">Batal</a>
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection

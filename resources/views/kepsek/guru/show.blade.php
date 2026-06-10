@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Detail Guru')
@section('page-title', 'Detail Guru')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('kepsek.guru.index') }}"
           class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-700 mb-4">
            ← Kembali
        </a>

        {{-- Profile header --}}
        <div class="card mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center sm:items-start">
                @if($guru->foto)
                    <img src="{{ asset('storage/' . $guru->foto) }}"
                         class="w-24 h-24 rounded-full object-cover" alt="Foto">
                @else
                    <div class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center
                                text-primary-700 text-3xl font-semibold">
                        {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 text-center sm:text-left">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $guru->nama_lengkap }}</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $guru->jabatan ?: 'Guru' }}{{ $guru->mata_pelajaran ? ' · ' . $guru->mata_pelajaran : '' }}
                    </p>
                    <div class="flex flex-wrap gap-2 justify-center sm:justify-start mt-3">
                        @if($guru->is_active)
                            <span class="badge badge-hadir">Aktif</span>
                        @else
                            <span class="badge badge-alpa">Nonaktif</span>
                        @endif
                        <span class="badge bg-gray-100 text-gray-700">{{ $guru->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                </div>
                <a href="{{ route('kepsek.guru.edit', $guru) }}" class="btn-secondary">
                    <x-sidebar-icon name="pencil" class="w-4 h-4 mr-1" />
                    Edit
                </a>
            </div>
        </div>

        {{-- Detail informasi --}}
        <div class="card">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Informasi</h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">NIP</dt>
                    <dd class="font-medium text-gray-800 font-mono">{{ $guru->nip ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Username</dt>
                    <dd class="font-medium text-gray-800 font-mono">{{ $guru->username }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-800">{{ $guru->email ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">No. HP</dt>
                    <dd class="font-medium text-gray-800">{{ $guru->no_hp ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Tempat, Tanggal Lahir</dt>
                    <dd class="font-medium text-gray-800">
                        {{ $guru->tempat_lahir ?? '-' }}{{ $guru->tgl_lahir ? ', ' . $guru->tgl_lahir->translatedFormat('d F Y') : '' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Agama</dt>
                    <dd class="font-medium text-gray-800">{{ $guru->agama ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Alamat</dt>
                    <dd class="font-medium text-gray-800">{{ $guru->alamat ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Token Barcode</dt>
                    <dd class="font-medium text-gray-800 font-mono">{{ $guru->barcode_token }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection

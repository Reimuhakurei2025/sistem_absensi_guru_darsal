@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Greeting --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">
            Selamat datang, {{ auth('kepsek')->user()->nama_lengkap }} 👋
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Hari ini, {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <x-stat-card
            label="Total Guru Aktif"
            :value="$totalGuru"
            icon="users"
            color="primary"
            :subtitle="$totalGuruNonaktif > 0 ? $totalGuruNonaktif . ' nonaktif' : null" />

        <x-stat-card
            label="Hadir Hari Ini"
            :value="$hadirHariIni"
            icon="check"
            color="green" />

        <x-stat-card
            label="Izin / Sakit"
            :value="$izinHariIni + $sakitHariIni"
            icon="document"
            color="yellow"
            subtitle="{{ $izinHariIni }} izin · {{ $sakitHariIni }} sakit" />

        <x-stat-card
            label="Belum Absen"
            :value="max(0, $belumAbsen)"
            icon="clock"
            color="red" />
    </div>

    {{-- Quick Actions --}}
    <div class="card mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Aksi Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <a href="{{ route('kepsek.guru.create') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200
                      hover:border-primary-400 hover:bg-primary-50 transition-colors text-center">
                <x-sidebar-icon name="plus" class="w-6 h-6 text-primary-700" />
                <span class="text-xs font-medium text-gray-700">Tambah Guru</span>
            </a>
            <a href="{{ route('kepsek.guru.index') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200
                      hover:border-primary-400 hover:bg-primary-50 transition-colors text-center">
                <x-sidebar-icon name="users" class="w-6 h-6 text-primary-700" />
                <span class="text-xs font-medium text-gray-700">Daftar Guru</span>
            </a>
            <a href="{{ route('kepsek.laporan.bulanan') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200
                      hover:border-primary-400 hover:bg-primary-50 transition-colors text-center">
                <x-sidebar-icon name="document" class="w-6 h-6 text-primary-700" />
                <span class="text-xs font-medium text-gray-700">Laporan</span>
            </a>
            <a href="{{ route('kepsek.password.index') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200
                      hover:border-primary-400 hover:bg-primary-50 transition-colors text-center">
                <x-sidebar-icon name="key" class="w-6 h-6 text-primary-700" />
                <span class="text-xs font-medium text-gray-700">Reset Password</span>
            </a>
        </div>
    </div>

    {{-- Absensi Terakhir Hari Ini --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Absensi Terakhir Hari Ini</h3>
            <a href="{{ route('kepsek.laporan.bulanan') }}" class="text-xs text-primary-700 hover:underline">
                Lihat semua →
            </a>
        </div>

        @if($absensiTerakhir->isEmpty())
            <p class="text-sm text-gray-500 text-center py-6">
                Belum ada guru yang melakukan absensi hari ini.
            </p>
        @else
            <div class="space-y-2">
                @foreach($absensiTerakhir as $item)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center
                                    text-primary-700 font-semibold text-sm flex-shrink-0">
                            {{ strtoupper(substr($item->guru->nama_lengkap, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-800 truncate">
                                {{ $item->guru->nama_lengkap }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $item->guru->jabatan ?: ($item->guru->mata_pelajaran ?? 'Guru') }}
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-semibold text-primary-700">
                                {{ \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') }}
                            </div>
                            <span class="badge badge-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

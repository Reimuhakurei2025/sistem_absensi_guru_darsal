@extends('layouts.app', ['role' => 'admin', 'currentUser' => auth('admin')->user()])

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Admin')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">
            Selamat datang, {{ auth('admin')->user()->nama_lengkap }} 👋
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">
        <x-stat-card label="Total Guru Aktif" :value="$totalGuru" icon="users" color="primary" />
        <x-stat-card label="Hadir Hari Ini" :value="$hadirHariIni" icon="check" color="green" />
        <x-stat-card label="Belum Absen" :value="max(0, $belumAbsen)" icon="clock" color="red" />
    </div>

    <div class="card">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Aksi Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            <a href="{{ route('admin.guru.index') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200
                      hover:border-primary-400 hover:bg-primary-50 transition-colors text-center">
                <x-sidebar-icon name="users" class="w-6 h-6 text-primary-700" />
                <span class="text-xs font-medium text-gray-700">Daftar Guru</span>
            </a>
            <a href="{{ route('admin.guru.cetak-semua') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200
                      hover:border-primary-400 hover:bg-primary-50 transition-colors text-center">
                <x-sidebar-icon name="qrcode" class="w-6 h-6 text-primary-700" />
                <span class="text-xs font-medium text-gray-700">Cetak Semua Barcode</span>
            </a>
        </div>
    </div>
@endsection

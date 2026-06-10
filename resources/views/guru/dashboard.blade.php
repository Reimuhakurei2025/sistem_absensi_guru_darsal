@extends('layouts.app', ['role' => 'guru', 'currentUser' => $guru])

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Greeting --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">
            Halo, {{ explode(',', $guru->nama_lengkap)[0] }} 👋
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>

    {{-- ========== STATUS ABSENSI HARI INI ========== --}}
    <div class="card mb-6 {{ $absensiHariIni ? 'bg-gradient-to-br from-green-50 to-white border-green-200' : '' }}">
        @if($absensiHariIni)
            {{-- Sudah absen --}}
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-green-800">Anda Sudah Absen Hari Ini</h3>
                    <p class="text-sm text-green-700 mt-1">
                        Tercatat hadir pukul
                        <strong class="font-semibold">{{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }}</strong>
                        WIB
                    </p>
                </div>
            </div>
        @else
            {{-- Belum absen — tampilkan tombol scan besar --}}
            <div class="text-center py-4">
                <div class="w-16 h-16 rounded-full bg-primary-50 flex items-center justify-center mx-auto mb-3">
                    <x-sidebar-icon name="qrcode" class="w-8 h-8 text-primary-700" />
                </div>
                <h3 class="text-base font-semibold text-gray-800">Anda Belum Absen Hari Ini</h3>
                <p class="text-sm text-gray-500 mt-1 mb-4">
                    Silakan scan barcode Anda untuk mencatat kehadiran.
                </p>
                <a href="{{ route('guru.scan') }}" class="btn-primary inline-flex">
                    <x-sidebar-icon name="qrcode" class="w-5 h-5 mr-2" />
                    Scan Barcode Sekarang
                </a>
            </div>
        @endif
    </div>

    {{-- ========== STATISTIK BULAN INI ========== --}}
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            Statistik {{ now()->translatedFormat('F Y') }}
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <x-stat-card label="Hadir" :value="$statistikBulanIni['hadir']" icon="check"    color="green" />
            <x-stat-card label="Izin"  :value="$statistikBulanIni['izin']"  icon="document" color="yellow" />
            <x-stat-card label="Sakit" :value="$statistikBulanIni['sakit']" icon="document" color="blue" />
            <x-stat-card label="Alpa"  :value="$statistikBulanIni['alpa']"  icon="document" color="red" />
        </div>
    </div>

    {{-- ========== RIWAYAT TERAKHIR ========== --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Riwayat Terakhir</h3>
            <a href="{{ route('guru.riwayat') }}" class="text-xs text-primary-700 hover:underline">
                Lihat semua →
            </a>
        </div>

        @if($riwayatTerakhir->isEmpty())
            <p class="text-sm text-gray-500 text-center py-6">
                Belum ada riwayat absensi.
            </p>
        @else
            <div class="space-y-2">
                @foreach($riwayatTerakhir as $item)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                        <div class="w-10 h-10 rounded-lg bg-white flex flex-col items-center justify-center
                                    flex-shrink-0 shadow-sm">
                            <div class="text-[10px] font-medium text-gray-500 uppercase">
                                {{ $item->tanggal->translatedFormat('M') }}
                            </div>
                            <div class="text-sm font-bold text-gray-800 leading-none">
                                {{ $item->tanggal->format('d') }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-800">
                                {{ $item->tanggal->translatedFormat('l') }}
                            </div>
                            @if($item->jam_masuk)
                                <div class="text-xs text-gray-500">
                                    Masuk: {{ \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') }} WIB
                                </div>
                            @endif
                        </div>
                        <span class="badge badge-{{ $item->status }} flex-shrink-0">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

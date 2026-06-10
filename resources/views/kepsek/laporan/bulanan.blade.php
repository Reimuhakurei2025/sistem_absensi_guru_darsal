@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Laporan Bulanan')
@section('page-title', 'Laporan Absensi Bulanan')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd; }
        @page { size: A4 landscape; margin: 1.5cm; }
    }
</style>
@endpush

@section('content')
    {{-- Filter --}}
    <div class="card mb-4 no-print">
        <form method="GET" action="{{ route('kepsek.laporan.bulanan') }}" class="flex flex-col sm:flex-row gap-2">
            <select name="bulan" class="form-input sm:w-48" onchange="this.form.submit()">
                @foreach(range(1, 12) as $b)
                    <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <select name="tahun" class="form-input sm:w-32" onchange="this.form.submit()">
                @foreach(range(now()->year - 2, now()->year) as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary">Tampilkan</button>

            {{-- Dropdown export --}}
            <div class="relative inline-block" x-data="{ open: false }" @click.away="open = false">
                <button type="button" @click="open = !open" class="btn-secondary">
                    📥 Ekspor
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak
                     class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-10 overflow-hidden">
                    <button type="button" @click="window.print(); open = false"
                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm flex items-center gap-2">
                        🖨️ Cetak (Browser)
                    </button>
                    <a href="{{ route('kepsek.laporan.bulanan.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm flex items-center gap-2 border-t border-gray-100">
                        📄 Download PDF
                    </a>
                    <a href="{{ route('kepsek.laporan.bulanan.word', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm flex items-center gap-2 border-t border-gray-100">
                        📝 Download Word
                    </a>
                    <a href="{{ route('kepsek.laporan.bulanan.excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm flex items-center gap-2 border-t border-gray-100">
                        📊 Download Excel
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Header laporan untuk print --}}
    <div class="hidden print:block text-center mb-6">
        <img src="{{ asset('images/logo-darussalam.png') }}" class="w-16 h-16 mx-auto mb-2" alt="Logo">
        <h1 class="text-lg font-bold text-primary-800">SMP TERPADU DARUSSALAM</h1>
        <p class="text-sm text-gray-600">Bojongsari, Depok</p>
        <h2 class="text-base font-semibold mt-3">
            Laporan Absensi Guru — {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
        </h2>
    </div>

    {{-- Summary stats --}}
    @php
        $totalHadir = $gurus->sum('jumlah_hadir');
        $totalIzin  = $gurus->sum('jumlah_izin');
        $totalSakit = $gurus->sum('jumlah_sakit');
        $totalAlpa  = $gurus->sum('jumlah_alpa');
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4 no-print">
        <x-stat-card label="Total Hadir" :value="$totalHadir" icon="check"    color="green" />
        <x-stat-card label="Total Izin"  :value="$totalIzin"  icon="document" color="yellow" />
        <x-stat-card label="Total Sakit" :value="$totalSakit" icon="document" color="blue" />
        <x-stat-card label="Total Alpa"  :value="$totalAlpa"  icon="document" color="red" />
    </div>

    @if($gurus->isEmpty())
        <x-empty-state
            icon="document"
            title="Belum ada data"
            message="Tidak ada guru aktif untuk ditampilkan." />
    @else
        {{-- Tabel rekap --}}
        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama Guru</th>
                            <th class="px-4 py-3 text-left">NIP</th>
                            <th class="px-4 py-3 text-center bg-green-50">Hadir</th>
                            <th class="px-4 py-3 text-center bg-yellow-50">Izin</th>
                            <th class="px-4 py-3 text-center bg-blue-50">Sakit</th>
                            <th class="px-4 py-3 text-center bg-red-50">Alpa</th>
                            <th class="px-4 py-3 text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($gurus as $i => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $row->guru->nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? 'Guru') }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $row->guru->nip ?: '-' }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-green-700">
                                    {{ $row->jumlah_hadir }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-yellow-700">
                                    {{ $row->jumlah_izin }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-blue-700">
                                    {{ $row->jumlah_sakit }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-red-700">
                                    {{ $row->jumlah_alpa }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-gray-800">
                                    {{ $row->total_absensi }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-gray-700">TOTAL</td>
                            <td class="px-4 py-3 text-center text-green-800">{{ $totalHadir }}</td>
                            <td class="px-4 py-3 text-center text-yellow-800">{{ $totalIzin }}</td>
                            <td class="px-4 py-3 text-center text-blue-800">{{ $totalSakit }}</td>
                            <td class="px-4 py-3 text-center text-red-800">{{ $totalAlpa }}</td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $totalHadir + $totalIzin + $totalSakit + $totalAlpa }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Tanda tangan untuk print --}}
        <div class="hidden print:grid mt-12 grid-cols-2 gap-8 text-sm">
            <div></div>
            <div class="text-center">
                <p>Depok, {{ now()->translatedFormat('d F Y') }}</p>
                <p>Kepala Sekolah,</p>
                <div class="h-20"></div>
                <p class="font-semibold underline">{{ auth('kepsek')->user()->nama_lengkap }}</p>
                <p class="text-xs">NIP. {{ auth('kepsek')->user()->nip ?? '-' }}</p>
            </div>
        </div>
    @endif
@endsection

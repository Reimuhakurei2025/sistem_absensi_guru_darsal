@php
    $currentUser = $role === 'kepsek' ? auth('kepsek')->user() : auth('admin')->user();
    $tglCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@extends('layouts.app', ['role' => $role, 'currentUser' => $currentUser])

@section('title', 'Laporan Harian')
@section('page-title', 'Laporan Harian')

@section('content')
    {{-- Filter + Export --}}
    <div class="card mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 items-end">
            <div class="flex-1">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}"
                       class="form-input" onchange="this.form.submit()">
            </div>

            <button type="submit" class="btn-primary">Tampilkan</button>

            {{-- Dropdown export --}}
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button type="button" @click="open = !open" class="btn-secondary">
                    📥 Ekspor
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak
                     class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-10 overflow-hidden">
                    <button type="button" @click="window.print(); open = false"
                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm">
                        🖨️ Cetak (Browser)
                    </button>
                    <a href="{{ route($role . '.laporan.harian.pdf', ['tanggal' => $tanggal]) }}"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm border-t border-gray-100">
                        📄 Download PDF
                    </a>
                    <a href="{{ route($role . '.laporan.harian.word', ['tanggal' => $tanggal]) }}"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm border-t border-gray-100">
                        📝 Download Word
                    </a>
                    <a href="{{ route($role . '.laporan.harian.excel', ['tanggal' => $tanggal]) }}"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm border-t border-gray-100">
                        📊 Download Excel
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Ringkasan Status --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
        <div class="card bg-green-50 border-green-200 text-center py-3">
            <div class="text-2xl font-bold text-green-700">{{ $ringkasan->hadir }}</div>
            <div class="text-xs text-green-600 mt-0.5">Hadir</div>
        </div>
        <div class="card bg-yellow-50 border-yellow-200 text-center py-3">
            <div class="text-2xl font-bold text-yellow-700">{{ $ringkasan->izin }}</div>
            <div class="text-xs text-yellow-600 mt-0.5">Izin</div>
        </div>
        <div class="card bg-blue-50 border-blue-200 text-center py-3">
            <div class="text-2xl font-bold text-blue-700">{{ $ringkasan->sakit }}</div>
            <div class="text-xs text-blue-600 mt-0.5">Sakit</div>
        </div>
        <div class="card bg-red-50 border-red-200 text-center py-3">
            <div class="text-2xl font-bold text-red-700">{{ $ringkasan->alpa }}</div>
            <div class="text-xs text-red-600 mt-0.5">Alpa</div>
        </div>
        <div class="card bg-gray-50 border-gray-200 text-center py-3 col-span-2 sm:col-span-1">
            <div class="text-2xl font-bold text-gray-500">{{ $ringkasan->belum }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Belum Absen</div>
        </div>
    </div>

    {{-- Kop Laporan (untuk print) --}}
    <div class="print-only text-center mb-4">
        <h2 class="text-lg font-bold">SMP TERPADU DARUSSALAM</h2>
        <p class="text-xs text-gray-500">Jl. Reni Jaya Timur IV Blok A 4/1 Pondok Petir, Bojongsari, Depok</p>
        <h3 class="text-base font-semibold text-primary-700 mt-2">LAPORAN ABSENSI HARIAN</h3>
        <p class="text-sm text-gray-600">{{ $tglCarbon->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Tabel --}}
    <div class="card p-0 overflow-hidden">
        <div class="bg-primary-700 px-4 py-2.5 text-white text-sm font-medium">
            {{ $tglCarbon->translatedFormat('l, d F Y') }} — {{ $ringkasan->total }} guru
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Nama Guru</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Jabatan / Mapel</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Jam Masuk</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Metode</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($data as $i => $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $row->guru->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500 md:hidden">
                                    {{ $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-') }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                                {{ $row->guru->jabatan ?: ($row->guru->mata_pelajaran ?? '-') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($row->status === 'belum')
                                    <span class="text-xs text-gray-400 italic">Belum</span>
                                @else
                                    <span class="badge badge-{{ $row->status }}">{{ ucfirst($row->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-xs">
                                {{ $row->jam_masuk ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs hidden lg:table-cell">
                                @if($row->input_method === 'scan')
                                    <span class="text-primary-600">Scan QR</span>
                                @elseif($row->input_method === 'manual')
                                    <span class="text-amber-600">Manual</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 hidden lg:table-cell">
                                {{ $row->keterangan ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

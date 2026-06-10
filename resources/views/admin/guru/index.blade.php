@extends('layouts.app', ['role' => 'admin', 'currentUser' => auth('admin')->user()])

@section('title', 'Daftar Guru')
@section('page-title', 'Daftar Guru')

@section('content')
    {{-- Search bar + Action --}}
    <div class="card mb-4">
        <div class="flex flex-col sm:flex-row gap-2">
            <form method="GET" action="{{ route('admin.guru.index') }}" class="flex-1 flex gap-2">
                <div class="flex-1 relative">
                    <x-sidebar-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-input pl-10" placeholder="Cari nama atau NIP guru...">
                </div>
                <button type="submit" class="btn-primary">Cari</button>
            </form>

            {{-- Dropdown Cetak Semua --}}
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button type="button" @click="open = !open" class="btn-secondary w-full sm:w-auto">
                    <x-sidebar-icon name="qrcode" class="w-4 h-4 mr-1" />
                    Cetak Semua Barcode
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak
                     class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-20 overflow-hidden">
                    <a href="{{ route('admin.guru.cetak-semua') }}" target="_blank"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm">
                        🖨️ Cetak (Browser)
                    </a>
                    <a href="{{ route('admin.guru.cetak-semua.pdf') }}"
                       class="block px-4 py-2.5 hover:bg-gray-50 text-sm border-t border-gray-100">
                        📄 Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($gurus->isEmpty())
        <x-empty-state
            icon="users"
            title="Tidak ada guru ditemukan"
            message="Coba kata kunci yang berbeda atau hubungi Kepala Sekolah." />
    @else
        {{-- Desktop tabel --}}
        <div class="hidden md:block card p-0 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">NIP</th>
                        <th class="px-4 py-3 text-left">Jabatan / Mapel</th>
                        <th class="px-4 py-3 text-right">Barcode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($gurus as $guru)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center
                                                text-primary-700 font-semibold text-xs">
                                        {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="font-medium text-gray-800">{{ $guru->nama_lengkap }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $guru->nip ?: '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                <div>{{ $guru->jabatan ?: '-' }}</div>
                                @if($guru->mata_pelajaran)
                                    <div class="text-xs text-gray-400">{{ $guru->mata_pelajaran }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1" x-data="{ open: false }" @click.away="open = false">
                                    <a href="{{ route('admin.guru.barcode', $guru) }}" target="_blank"
                                       class="btn-secondary text-xs">
                                        <x-sidebar-icon name="qrcode" class="w-4 h-4 mr-1" />
                                        Lihat
                                    </a>
                                    <div class="relative">
                                        <button type="button" @click="open = !open"
                                                class="btn-secondary text-xs px-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak
                                             class="absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-lg border border-gray-200 z-10 overflow-hidden text-left">
                                            <a href="{{ route('admin.guru.barcode', $guru) }}" target="_blank"
                                               class="block px-3 py-2 hover:bg-gray-50 text-xs">
                                                🖨️ Cetak (Browser)
                                            </a>
                                            <a href="{{ route('admin.guru.barcode.pdf', $guru) }}"
                                               class="block px-3 py-2 hover:bg-gray-50 text-xs border-t border-gray-100">
                                                📄 Download PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile card --}}
        <div class="md:hidden space-y-2">
            @foreach($gurus as $guru)
                <div class="card flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center
                                text-primary-700 font-semibold flex-shrink-0">
                        {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-800 truncate">{{ $guru->nama_lengkap }}</div>
                        <div class="text-xs text-gray-500 truncate">{{ $guru->jabatan ?: ($guru->mata_pelajaran ?? 'Guru') }}</div>
                        @if($guru->nip)
                            <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $guru->nip }}</div>
                        @endif
                    </div>
                    <a href="{{ route('admin.guru.barcode', $guru) }}" target="_blank"
                       class="p-2 rounded-lg bg-primary-50 text-primary-700 flex-shrink-0"
                       title="Lihat barcode">
                        <x-sidebar-icon name="qrcode" class="w-5 h-5" />
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $gurus->links() }}
        </div>
    @endif
@endsection

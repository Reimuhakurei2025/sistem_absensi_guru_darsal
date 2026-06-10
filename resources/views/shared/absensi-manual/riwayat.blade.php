@php
    $currentUser = $role === 'kepsek' ? auth('kepsek')->user() : auth('admin')->user();
@endphp

@extends('layouts.app', ['role' => $role, 'currentUser' => $currentUser])

@section('title', 'Riwayat Input Manual')
@section('page-title', 'Riwayat Input Manual')

@section('content')
    {{-- Tab nav --}}
    <div class="flex gap-1 mb-4 bg-white p-1 rounded-lg shadow-card overflow-x-auto">
        <a href="{{ route($role . '.absensi-manual.single') }}"
           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                  text-gray-600 hover:bg-gray-100 whitespace-nowrap">
            <x-sidebar-icon name="pencil" class="w-4 h-4" />
            Per Guru
        </a>
        <a href="{{ route($role . '.absensi-manual.bulk') }}"
           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                  text-gray-600 hover:bg-gray-100 whitespace-nowrap">
            <x-sidebar-icon name="users" class="w-4 h-4" />
            Massal
        </a>
        <a href="{{ route($role . '.absensi-manual.riwayat') }}"
           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                  bg-primary-700 text-white whitespace-nowrap">
            <x-sidebar-icon name="clock" class="w-4 h-4" />
            Riwayat
        </a>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2">
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
        </form>
    </div>

    @if($absensi->isEmpty())
        <x-empty-state
            icon="document"
            title="Belum ada input manual"
            message="Periode {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }} belum ada absensi yang diinput manual." />
    @else
        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Guru</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Keterangan</th>
                            <th class="px-4 py-3 text-left">Diinput Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($absensi as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="text-xs">{{ $a->tanggal->translatedFormat('l') }}</div>
                                    <div class="font-medium">{{ $a->tanggal->translatedFormat('d M Y') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $a->guru->nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500">{{ $a->guru->mata_pelajaran ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 hidden md:table-cell">
                                    {{ $a->keterangan ?: '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-gray-700">
                                        {{ $a->getInputByName() ?: '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 uppercase">
                                        {{ $a->input_by_role }} · {{ $a->updated_at->translatedFormat('d M, H:i') }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $absensi->links() }}
        </div>
    @endif
@endsection

@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Ranking Kehadiran')
@section('page-title', 'Ranking Kehadiran Guru')

@section('content')
    {{-- Filter --}}
    <div class="card mb-4">
        <form method="GET" action="{{ route('kepsek.laporan.ranking') }}" class="flex flex-col sm:flex-row gap-2">
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

    @if($ranking->isEmpty())
        <x-empty-state
            icon="trophy"
            title="Belum ada data ranking"
            message="Tidak ada guru aktif untuk diranking." />
    @else

        {{-- Subtitle period --}}
        <div class="text-center mb-6">
            <p class="text-sm text-gray-500">Periode</p>
            <h2 class="text-lg font-semibold text-gray-800">
                {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
            </h2>
        </div>

        {{-- ============== PODIUM TOP 3 ============== --}}
        @if($ranking->count() >= 1)
            <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-8 items-end max-w-2xl mx-auto">

                {{-- 2nd Place --}}
                @if($ranking->count() >= 2)
                    @php $second = $ranking[1]; @endphp
                    <div class="order-1 text-center">
                        <div class="relative inline-block mb-2">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full bg-gray-200 flex items-center justify-center
                                        text-gray-700 font-bold text-2xl border-4 border-gray-400">
                                {{ strtoupper(substr($second->nama_lengkap, 0, 1)) }}
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-gray-400 text-white
                                        flex items-center justify-center text-sm font-bold shadow-lg">
                                2
                            </div>
                        </div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-800 truncate px-1">
                            {{ $second->nama_lengkap }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ $second->mata_pelajaran ?? '-' }}</p>
                        <div class="mt-2 bg-gradient-to-b from-gray-300 to-gray-400 text-white rounded-t-lg p-3 h-24 sm:h-28 flex flex-col justify-end">
                            <p class="text-xl sm:text-2xl font-bold">{{ $second->hadir_count }}</p>
                            <p class="text-[10px] uppercase opacity-90">Hadir</p>
                        </div>
                    </div>
                @endif

                {{-- 1st Place --}}
                @php $first = $ranking[0]; @endphp
                <div class="order-2 text-center">
                    <div class="text-2xl mb-1">👑</div>
                    <div class="relative inline-block mb-2">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-full bg-yellow-100 flex items-center justify-center
                                    text-yellow-700 font-bold text-3xl border-4 border-yellow-400 shadow-lg">
                            {{ strtoupper(substr($first->nama_lengkap, 0, 1)) }}
                        </div>
                        <div class="absolute -top-2 -right-2 w-9 h-9 rounded-full bg-yellow-400 text-white
                                    flex items-center justify-center text-base font-bold shadow-lg">
                            1
                        </div>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-800 truncate px-1">
                        {{ $first->nama_lengkap }}
                    </h3>
                    <p class="text-xs text-gray-500">{{ $first->mata_pelajaran ?? '-' }}</p>
                    <div class="mt-2 bg-gradient-to-b from-yellow-400 to-yellow-500 text-white rounded-t-lg p-3 h-32 sm:h-36 flex flex-col justify-end">
                        <p class="text-2xl sm:text-3xl font-bold">{{ $first->hadir_count }}</p>
                        <p class="text-[10px] uppercase opacity-90">Hadir</p>
                    </div>
                </div>

                {{-- 3rd Place --}}
                @if($ranking->count() >= 3)
                    @php $third = $ranking[2]; @endphp
                    <div class="order-3 text-center">
                        <div class="relative inline-block mb-2">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full bg-orange-100 flex items-center justify-center
                                        text-orange-700 font-bold text-2xl border-4 border-orange-400">
                                {{ strtoupper(substr($third->nama_lengkap, 0, 1)) }}
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-orange-400 text-white
                                        flex items-center justify-center text-sm font-bold shadow-lg">
                                3
                            </div>
                        </div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-800 truncate px-1">
                            {{ $third->nama_lengkap }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ $third->mata_pelajaran ?? '-' }}</p>
                        <div class="mt-2 bg-gradient-to-b from-orange-300 to-orange-400 text-white rounded-t-lg p-3 h-20 sm:h-24 flex flex-col justify-end">
                            <p class="text-xl sm:text-2xl font-bold">{{ $third->hadir_count }}</p>
                            <p class="text-[10px] uppercase opacity-90">Hadir</p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ============== FULL LIST ============== --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Daftar Lengkap</h3>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($ranking as $i => $guru)
                    @php
                        $rank = $i + 1;
                        $rankColor = match(true) {
                            $rank === 1 => 'bg-yellow-100 text-yellow-700 border-yellow-300',
                            $rank === 2 => 'bg-gray-100 text-gray-700 border-gray-300',
                            $rank === 3 => 'bg-orange-100 text-orange-700 border-orange-300',
                            default     => 'bg-gray-50 text-gray-600 border-gray-200',
                        };
                    @endphp

                    <div class="flex items-center gap-3 p-4 hover:bg-gray-50">
                        {{-- Rank number --}}
                        <div class="w-10 h-10 rounded-full border-2 {{ $rankColor }}
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ $rank }}
                        </div>

                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center
                                    text-primary-700 font-semibold flex-shrink-0">
                            {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-800 truncate">{{ $guru->nama_lengkap }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $guru->mata_pelajaran ?? 'Guru' }}</div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex gap-3 text-xs flex-shrink-0">
                            <div class="text-center">
                                <div class="font-bold text-green-700">{{ $guru->hadir_count }}</div>
                                <div class="text-gray-500">Hadir</div>
                            </div>
                            <div class="text-center hidden sm:block">
                                <div class="font-bold text-yellow-700">{{ $guru->izin_count }}</div>
                                <div class="text-gray-500">Izin</div>
                            </div>
                            <div class="text-center hidden sm:block">
                                <div class="font-bold text-red-700">{{ $guru->alpa_count }}</div>
                                <div class="text-gray-500">Alpa</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

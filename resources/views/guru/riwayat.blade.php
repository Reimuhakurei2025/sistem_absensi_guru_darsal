@extends('layouts.app', ['role' => 'guru', 'currentUser' => auth('guru')->user()])

@section('title', 'Riwayat Absensi')
@section('page-title', 'Riwayat Absensi')

@section('content')
    {{-- Filter --}}
    <div class="card mb-4">
        <form method="GET" action="{{ route('guru.riwayat') }}" class="flex flex-col sm:flex-row gap-2">
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

            <button type="submit" class="btn-primary sm:w-auto">Tampilkan</button>
        </form>
    </div>

    @if($riwayat->isEmpty())
        <x-empty-state
            icon="clock"
            title="Tidak ada riwayat"
            message="Belum ada absensi tercatat untuk periode {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}." />
    @else
        {{-- List riwayat --}}
        <div class="card p-0 overflow-hidden">
            <div class="divide-y divide-gray-100">
                @foreach($riwayat as $item)
                    <div class="flex items-center gap-4 p-4 hover:bg-gray-50">
                        {{-- Tanggal box --}}
                        <div class="w-12 h-12 rounded-lg bg-primary-50 flex flex-col items-center justify-center
                                    flex-shrink-0">
                            <div class="text-[10px] font-medium text-primary-700 uppercase">
                                {{ $item->tanggal->translatedFormat('M') }}
                            </div>
                            <div class="text-base font-bold text-primary-800 leading-none">
                                {{ $item->tanggal->format('d') }}
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-800">
                                {{ $item->tanggal->translatedFormat('l, d F Y') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5 flex flex-wrap gap-3">
                                @if($item->jam_masuk)
                                    <span>🕐 Masuk: {{ \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') }}</span>
                                @endif
                                @if($item->keterangan)
                                    <span class="truncate">📝 {{ $item->keterangan }}</span>
                                @endif
                            </div>
                        </div>

                        <span class="badge badge-{{ $item->status }} flex-shrink-0">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4">
            {{ $riwayat->links() }}
        </div>
    @endif
@endsection

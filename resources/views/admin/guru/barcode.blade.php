@extends('layouts.print')

@section('title', 'Barcode ' . $guru->nama_lengkap)

@section('toolbar-extra')
    <a href="{{ route('admin.guru.barcode.pdf', $guru) }}"
       class="btn-secondary text-sm">
        📄 Save as PDF
    </a>
@endsection

@section('content')
    <div class="print-area bg-white rounded-2xl shadow-card p-8 max-w-md mx-auto">
        {{-- Header --}}
        <div class="text-center mb-5 border-b border-gray-200 pb-5">
            <img src="{{ asset('images/logo-darussalam.png') }}" alt="Logo"
                 class="w-16 h-16 mx-auto mb-2">
            <h1 class="font-bold text-primary-800 text-base">SMP TERPADU DARUSSALAM</h1>
            <p class="text-xs text-gray-500">Bojongsari, Depok</p>
            <p class="text-xs text-gray-500 mt-1 font-medium">Kartu Absensi Guru</p>
        </div>

        {{-- QR Code --}}
        <div class="flex justify-center my-5">
            <div class="p-4 bg-white border-2 border-primary-100 rounded-xl">
                {!! $qrCode !!}
            </div>
        </div>

        {{-- Info guru --}}
        <div class="text-center mb-5">
            <h2 class="text-lg font-bold text-gray-800">{{ $guru->nama_lengkap }}</h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ $guru->jabatan ?: 'Guru' }}{{ $guru->mata_pelajaran ? ' · ' . $guru->mata_pelajaran : '' }}
            </p>
            @if($guru->nip)
                <p class="text-xs text-gray-400 font-mono mt-2">NIP: {{ $guru->nip }}</p>
            @endif
        </div>

        {{-- Token --}}
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Token Barcode</div>
            <div class="font-mono text-sm font-semibold text-gray-800 mt-0.5">
                {{ $guru->barcode_token }}
            </div>
        </div>

        {{-- Footer info --}}
        <div class="text-center mt-5 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400">
                Scan QR Code ini di halaman absensi sistem
            </p>
        </div>
    </div>
@endsection

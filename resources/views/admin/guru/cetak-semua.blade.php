@extends('layouts.print')

@section('title', 'Cetak Semua Barcode')

@section('toolbar-extra')
    <a href="{{ route('admin.guru.cetak-semua.pdf') }}"
       class="btn-secondary text-sm">
        📄 Save as PDF
    </a>
@endsection

@section('content')
    <div class="print-area">
        {{-- Title (no print) --}}
        <div class="no-print mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Cetak Semua Barcode</h1>
            <p class="text-sm text-gray-500 mt-1">
                Total {{ $gurus->count() }} guru aktif. Cetak halaman ini, lalu potong menjadi kartu individual.
            </p>
        </div>

        @if($gurus->isEmpty())
            <div class="text-center py-12 text-gray-500">
                Belum ada guru aktif untuk dicetak barcode-nya.
            </div>
        @else
            <div class="grid grid-cols-2 gap-4">
                @foreach($gurus as $guru)
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-white
                                break-inside-avoid">
                        <div class="text-center mb-2 border-b border-gray-100 pb-2">
                            <img src="{{ asset('images/logo-darussalam.png') }}"
                                 class="w-10 h-10 mx-auto mb-1" alt="Logo">
                            <div class="text-[10px] font-bold text-primary-800 leading-tight">
                                SMP TERPADU DARUSSALAM
                            </div>
                            <div class="text-[9px] text-gray-500">Kartu Absensi Guru</div>
                        </div>

                        <div class="flex justify-center my-2">
                            <div class="p-1 bg-white border border-primary-100 rounded">
                                {!! $qrCodes[$guru->id_guru] !!}
                            </div>
                        </div>

                        <div class="text-center">
                            <div class="font-bold text-xs text-gray-800 leading-tight">
                                {{ $guru->nama_lengkap }}
                            </div>
                            <div class="text-[10px] text-gray-600 mt-0.5">
                                {{ $guru->jabatan ?: ($guru->mata_pelajaran ?? 'Guru') }}
                            </div>
                            @if($guru->nip)
                                <div class="text-[9px] text-gray-400 font-mono mt-0.5">
                                    {{ $guru->nip }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <style>
        @media print {
            .grid { gap: 0.5cm !important; }
            .break-inside-avoid { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
@endsection

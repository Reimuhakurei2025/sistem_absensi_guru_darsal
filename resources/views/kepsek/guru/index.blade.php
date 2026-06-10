@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Manajemen Guru')
@section('page-title', 'Manajemen Guru')

@section('content')
    {{-- Toolbar: Search + Filter + Tambah --}}
    <div class="card mb-4">
        <form method="GET" action="{{ route('kepsek.guru.index') }}"
              class="flex flex-col sm:flex-row gap-2">

            {{-- Search input --}}
            <div class="flex-1 relative">
                <x-sidebar-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       class="form-input pl-10"
                       placeholder="Cari nama, NIP, atau mata pelajaran...">
            </div>

            {{-- Filter status --}}
            <select name="status" class="form-input sm:w-44"
                    onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif"    {{ request('status') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button type="submit" class="btn-secondary sm:w-auto">Cari</button>

            <a href="{{ route('kepsek.guru.create') }}" class="btn-primary sm:w-auto">
                <x-sidebar-icon name="plus" class="w-4 h-4 mr-1" />
                Tambah
            </a>
        </form>
    </div>

    @if($gurus->isEmpty())
        <x-empty-state
            icon="users"
            title="Belum ada guru terdaftar"
            message="{{ request()->hasAny(['q', 'status']) ? 'Tidak ada guru yang cocok dengan filter Anda.' : 'Mulai dengan menambahkan guru pertama.' }}">
            <x-slot:action>
                <a href="{{ route('kepsek.guru.create') }}" class="btn-primary">
                    Tambah Guru
                </a>
            </x-slot:action>
        </x-empty-state>
    @else
        {{-- Desktop: Tabel --}}
        <div class="hidden md:block card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama Lengkap</th>
                            <th class="px-4 py-3 text-left">NIP</th>
                            <th class="px-4 py-3 text-left">Jabatan / Mapel</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($gurus as $guru)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center
                                                    text-primary-700 font-semibold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800">{{ $guru->nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ $guru->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $guru->nip ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-700">{{ $guru->jabatan ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $guru->mata_pelajaran ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($guru->is_active)
                                        <span class="badge badge-hadir">Aktif</span>
                                    @else
                                        <span class="badge badge-alpa">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('kepsek.guru.edit', $guru) }}"
                                           class="p-1.5 rounded hover:bg-gray-200 text-gray-600" title="Edit">
                                            <x-sidebar-icon name="pencil" class="w-4 h-4" />
                                        </a>
                                        @if($guru->is_active)
                                            <form id="form-deactivate-{{ $guru->id_guru }}"
                                                  method="POST"
                                                  action="{{ route('kepsek.guru.deactivate', $guru) }}"
                                                  class="inline">
                                                @csrf
                                                <button type="button"
                                                        onclick="confirmDeactivate({{ $guru->id_guru }}, '{{ addslashes($guru->nama_lengkap) }}')"
                                                        class="p-1.5 rounded hover:bg-red-50 text-red-600" title="Nonaktifkan">
                                                    <x-sidebar-icon name="trash" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        @else
                                            <form id="form-activate-{{ $guru->id_guru }}"
                                                  method="POST"
                                                  action="{{ route('kepsek.guru.activate', $guru) }}"
                                                  class="inline">
                                                @csrf
                                                <button type="button"
                                                        onclick="confirmActivate({{ $guru->id_guru }}, '{{ addslashes($guru->nama_lengkap) }}')"
                                                        class="p-1.5 rounded hover:bg-green-50 text-green-600" title="Aktifkan">
                                                    <x-sidebar-icon name="check" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: Card list --}}
        <div class="md:hidden space-y-2">
            @foreach($gurus as $guru)
                <div class="card p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center
                                    text-primary-700 font-semibold flex-shrink-0">
                            {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-800 truncate">{{ $guru->nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $guru->mata_pelajaran ?? 'Guru' }}</div>
                                </div>
                                @if($guru->is_active)
                                    <span class="badge badge-hadir flex-shrink-0">Aktif</span>
                                @else
                                    <span class="badge badge-alpa flex-shrink-0">Nonaktif</span>
                                @endif
                            </div>
                            @if($guru->nip)
                                <div class="text-xs text-gray-400 mt-1 font-mono">NIP: {{ $guru->nip }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('kepsek.guru.edit', $guru) }}" class="btn-secondary text-xs flex-1">Edit</a>
                        @if($guru->is_active)
                            <form id="form-deactivate-mobile-{{ $guru->id_guru }}"
                                  method="POST" action="{{ route('kepsek.guru.deactivate', $guru) }}" class="flex-1">
                                @csrf
                                <button type="button"
                                        onclick="confirmDeactivate({{ $guru->id_guru }}, '{{ addslashes($guru->nama_lengkap) }}', 'mobile')"
                                        class="btn-danger text-xs w-full">
                                    Nonaktifkan
                                </button>
                            </form>
                        @else
                            <form id="form-activate-mobile-{{ $guru->id_guru }}"
                                  method="POST" action="{{ route('kepsek.guru.activate', $guru) }}" class="flex-1">
                                @csrf
                                <button type="button"
                                        onclick="confirmActivate({{ $guru->id_guru }}, '{{ addslashes($guru->nama_lengkap) }}', 'mobile')"
                                        class="btn-primary text-xs w-full">
                                    Aktifkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $gurus->links() }}
        </div>
    @endif

    {{-- Modal Konfirmasi Password --}}
    <x-password-confirm-modal />
@endsection

@push('scripts')
<script>
function confirmDeactivate(id, nama, suffix = '') {
    const formId = suffix ? `form-deactivate-${suffix}-${id}` : `form-deactivate-${id}`;

    // Update teks modal
    document.getElementById('password-confirm-modal-title').textContent = 'Nonaktifkan Guru?';
    document.getElementById('password-confirm-modal-message').textContent =
        `Anda akan menonaktifkan guru "${nama}". Masukkan password Anda untuk konfirmasi.`;

    openPasswordModal(formId);
}

function confirmActivate(id, nama, suffix = '') {
    const formId = suffix ? `form-activate-${suffix}-${id}` : `form-activate-${id}`;

    document.getElementById('password-confirm-modal-title').textContent = 'Aktifkan Kembali Guru?';
    document.getElementById('password-confirm-modal-message').textContent =
        `Anda akan mengaktifkan kembali guru "${nama}". Masukkan password Anda untuk konfirmasi.`;

    openPasswordModal(formId);
}
</script>
@endpush

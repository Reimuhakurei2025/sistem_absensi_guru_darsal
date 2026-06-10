@php
    $currentUser = $role === 'kepsek' ? auth('kepsek')->user() : auth('admin')->user();
@endphp

@extends('layouts.app', ['role' => $role, 'currentUser' => $currentUser])

@section('title', 'Input Absensi Massal')
@section('page-title', 'Input Absensi Massal')

@section('content')
    <div class="max-w-5xl">

        {{-- Tab navigation --}}
        <div class="flex gap-1 mb-4 bg-white p-1 rounded-lg shadow-card overflow-x-auto">
            <a href="{{ route($role . '.absensi-manual.single') }}"
               class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                      text-gray-600 hover:bg-gray-100 whitespace-nowrap">
                <x-sidebar-icon name="pencil" class="w-4 h-4" />
                Per Guru
            </a>
            <a href="{{ route($role . '.absensi-manual.bulk') }}"
               class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                      bg-primary-700 text-white whitespace-nowrap">
                <x-sidebar-icon name="users" class="w-4 h-4" />
                Massal
            </a>
            <a href="{{ route($role . '.absensi-manual.riwayat') }}"
               class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                      text-gray-600 hover:bg-gray-100 whitespace-nowrap">
                <x-sidebar-icon name="clock" class="w-4 h-4" />
                Riwayat
            </a>
        </div>

        <form id="form-bulk" method="POST" action="{{ route($role . '.absensi-manual.bulk.store') }}">
            @csrf

            {{-- Header: Tanggal + Bulk Action --}}
            <div class="card mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-3">Input Absensi Massal</h3>

                <div class="grid sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="form-label">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="input-tanggal"
                               value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                               class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label">Set Semua Sekaligus</label>
                        <select id="bulk-action" class="form-input"
                                onchange="applyBulkAction(this.value)">
                            <option value="">-- Pilih --</option>
                            <option value="hadir">✓ Semua Hadir</option>
                            <option value="izin">📋 Semua Izin</option>
                            <option value="sakit">🤒 Semua Sakit</option>
                            <option value="alpa">✗ Semua Alpa</option>
                            <option value="skip">⊘ Skip Semua</option>
                        </select>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">
                            Tip: Gunakan "Set Semua" lalu ubah individual yang berbeda.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Warning overwrite area --}}
            <div id="warning-overwrite"
                 class="p-3 mb-4 rounded-lg bg-yellow-50 border border-yellow-200 text-sm text-yellow-800 hidden">
                <strong>⚠️ Ada <span id="overwrite-count">0</span> absensi yang akan di-overwrite.</strong>
                Klik Simpan akan meminta password Anda untuk konfirmasi.
            </div>

            {{-- Tabel Guru --}}
            <div class="card p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left">Guru</th>
                                <th class="px-4 py-3 text-center w-40">Status</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">Keterangan</th>
                                <th class="px-4 py-3 text-center w-24">Existing</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($gurus as $i => $g)
                                <tr class="hover:bg-gray-50" data-id-guru="{{ $g->id_guru }}">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="entries[{{ $i }}][id_guru]" value="{{ $g->id_guru }}">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center
                                                        text-primary-700 font-semibold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($g->nama_lengkap, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-800">{{ $g->nama_lengkap }}</div>
                                                <div class="text-xs text-gray-500">{{ $g->mata_pelajaran ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="entries[{{ $i }}][status]"
                                                class="form-input py-1.5 text-xs status-select" required>
                                            <option value="skip">⊘ Skip</option>
                                            <option value="hadir">✓ Hadir</option>
                                            <option value="izin">📋 Izin</option>
                                            <option value="sakit">🤒 Sakit</option>
                                            <option value="alpa">✗ Alpa</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 hidden lg:table-cell">
                                        <input type="text" name="entries[{{ $i }}][keterangan]"
                                               class="form-input py-1.5 text-xs"
                                               placeholder="Keterangan (opsional)">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="existing-badge text-xs text-gray-400">—</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer action --}}
            <div class="sticky bottom-4 mt-4 flex gap-2 justify-end">
                <a href="{{ route($role . '.dashboard') }}" class="btn-secondary">Batal</a>
                <button type="button" onclick="handleBulkSubmit()" class="btn-primary">
                    <span id="btn-text">Simpan Semua</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Modal konfirmasi password (jika ada overwrite) --}}
    <x-password-confirm-modal
        id="confirm-password-modal"
        title="Konfirmasi Overwrite Massal"
        message="Beberapa absensi akan di-overwrite. Masukkan password Anda."
        formId="form-bulk"
        inputName="confirm_password"
        buttonText="Konfirmasi" />
@endsection

@push('scripts')
<script>
const CHECK_URL = "{{ route($role . '.absensi-manual.check') }}";
const CSRF = "{{ csrf_token() }}";

let overwriteCount = 0;

// Apply bulk action ke semua row
function applyBulkAction(value) {
    if (!value) return;
    document.querySelectorAll('.status-select').forEach(sel => {
        sel.value = value;
    });
}

// Cek existing untuk semua guru di tanggal terpilih
async function checkAllExisting() {
    const tgl = document.getElementById('input-tanggal').value;
    if (!tgl) return;

    const rows = document.querySelectorAll('tr[data-id-guru]');
    overwriteCount = 0;

    // Reset semua badge
    rows.forEach(row => {
        row.querySelector('.existing-badge').textContent = '—';
        row.querySelector('.existing-badge').className = 'existing-badge text-xs text-gray-400';
    });

    // Cek per guru (paralel)
    const checks = Array.from(rows).map(async (row) => {
        const guruId = row.dataset.idGuru;
        try {
            const res = await fetch(CHECK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ id_guru: guruId, tanggal: tgl })
            });
            const data = await res.json();
            if (data.exists) {
                const badge = row.querySelector('.existing-badge');
                const labels = { hadir: 'Hadir', izin: 'Izin', sakit: 'Sakit', alpa: 'Alpa' };
                badge.textContent = labels[data.status] ?? data.status;
                badge.className = `existing-badge badge badge-${data.status}`;
            }
        } catch (e) {
            console.warn('Check failed for guru', guruId);
        }
    });

    await Promise.all(checks);
    updateOverwriteWarning();
}

// Update warning sesuai pilihan status (skip vs non-skip dari yang exists)
function updateOverwriteWarning() {
    let count = 0;
    document.querySelectorAll('tr[data-id-guru]').forEach(row => {
        const status = row.querySelector('.status-select').value;
        const hasExisting = row.querySelector('.existing-badge').textContent !== '—';
        if (hasExisting && status !== 'skip') count++;
    });

    overwriteCount = count;
    const warning = document.getElementById('warning-overwrite');
    const btnText = document.getElementById('btn-text');

    if (count > 0) {
        warning.classList.remove('hidden');
        document.getElementById('overwrite-count').textContent = count;
        btnText.textContent = `Simpan & Overwrite (${count})`;
    } else {
        warning.classList.add('hidden');
        btnText.textContent = 'Simpan Semua';
    }
}

// Submit handler
function handleBulkSubmit() {
    // Cek apakah ada yang dipilih (selain skip)
    const anySelected = Array.from(document.querySelectorAll('.status-select'))
                             .some(s => s.value !== 'skip');

    if (!anySelected) {
        alert('Pilih minimal satu guru untuk diabsensi.');
        return;
    }

    if (overwriteCount > 0) {
        openPasswordModal('form-bulk', 'confirm-password-modal', 'confirm_password');
    } else {
        document.getElementById('form-bulk').submit();
    }
}

// Listeners
document.addEventListener('DOMContentLoaded', () => {
    checkAllExisting();
});
document.getElementById('input-tanggal').addEventListener('change', checkAllExisting);
document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', updateOverwriteWarning);
});
</script>
@endpush

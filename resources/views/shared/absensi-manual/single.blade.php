@php
    $currentUser = $role === 'kepsek' ? auth('kepsek')->user() : auth('admin')->user();
@endphp

@extends('layouts.app', ['role' => $role, 'currentUser' => $currentUser])

@section('title', 'Input Absensi Manual')
@section('page-title', 'Input Absensi Manual')

@section('content')
    <div class="max-w-3xl">

        {{-- Tab Single vs Bulk vs Riwayat --}}
        <div class="flex gap-1 mb-4 bg-white p-1 rounded-lg shadow-card overflow-x-auto">
            <a href="{{ route($role . '.absensi-manual.single') }}"
               class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                      bg-primary-700 text-white whitespace-nowrap">
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
                      text-gray-600 hover:bg-gray-100 whitespace-nowrap">
                <x-sidebar-icon name="clock" class="w-4 h-4" />
                Riwayat
            </a>
        </div>

        {{-- Info usage --}}
        <div class="card bg-primary-50 border-primary-200 mb-4 text-sm text-primary-900">
            <div class="flex items-start gap-2">
                <x-sidebar-icon name="document" class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <div>
                    <strong class="font-semibold">Kapan menggunakan fitur ini?</strong>
                    <ul class="mt-1 ml-4 list-disc text-xs space-y-0.5">
                        <li>Guru sakit/izin tidak dapat scan QR Code</li>
                        <li>Backdate (tanggal lalu) atau forward (izin mendatang)</li>
                        <li>Koreksi data — overwrite memerlukan konfirmasi password</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ============== FORM ============== --}}
        <form id="form-single" method="POST" action="{{ route($role . '.absensi-manual.single.store') }}"
              class="card space-y-4">
            @csrf

            <h3 class="text-base font-semibold text-gray-800">Input Per Guru</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Pilih Guru --}}
                <div class="sm:col-span-2">
                    <label class="form-label">Guru <span class="text-red-500">*</span></label>
                    <select name="id_guru" id="select-guru" class="form-input" required>
                        <option value="">Pilih guru...</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id_guru }}" {{ old('id_guru') == $g->id_guru ? 'selected' : '' }}>
                                {{ $g->nama_lengkap }} {{ $g->mata_pelajaran ? '· ' . $g->mata_pelajaran : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="form-label">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="input-tanggal"
                           value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                           class="form-input" required>
                </div>

                {{-- Status --}}
                <div>
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="select-status" class="form-input" required>
                        <option value="hadir" {{ old('status') === 'hadir' ? 'selected' : '' }}>✓ Hadir</option>
                        <option value="izin"  {{ old('status') === 'izin'  ? 'selected' : '' }}>📋 Izin</option>
                        <option value="sakit" {{ old('status') === 'sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                        <option value="alpa"  {{ old('status') === 'alpa'  ? 'selected' : '' }}>✗ Alpa</option>
                    </select>
                </div>

                {{-- Jam Masuk (hanya jika status=hadir) --}}
                <div id="container-jam-masuk">
                    <label class="form-label">Jam Masuk (opsional)</label>
                    <input type="time" name="jam_masuk" value="{{ old('jam_masuk') }}" class="form-input">
                </div>

                {{-- Keterangan --}}
                <div class="sm:col-span-2">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="form-input"
                              placeholder="contoh: sakit demam, surat dokter terlampir">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            {{-- Warning overwrite (auto-show via JS jika exists) --}}
            <div id="warning-overwrite"
                 class="p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-sm text-yellow-800 hidden">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <strong>Sudah ada absensi:</strong>
                        <span id="overwrite-info"></span>
                        <p class="text-xs mt-1">Klik <strong>Simpan</strong> akan meminta password Anda untuk konfirmasi overwrite.</p>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <button type="button" onclick="handleSubmit()" class="btn-primary w-full sm:w-auto">
                    <span id="btn-submit-text">Simpan Absensi</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Modal Konfirmasi Password (hanya dipakai jika overwrite) --}}
    <x-password-confirm-modal
        id="confirm-password-modal"
        title="Konfirmasi Overwrite"
        message="Data absensi sebelumnya akan diganti. Masukkan password Anda."
        formId="form-single"
        inputName="confirm_password"
        buttonText="Konfirmasi Overwrite" />
@endsection

@push('scripts')
<script>
const CHECK_URL = "{{ route($role . '.absensi-manual.check') }}";
const CSRF = "{{ csrf_token() }}";

let isOverwrite = false;

// Show/hide jam masuk berdasarkan status
function toggleJamMasuk() {
    const status = document.getElementById('select-status').value;
    const container = document.getElementById('container-jam-masuk');
    container.style.display = (status === 'hadir') ? '' : 'none';
}

// Cek existing absensi via AJAX
async function checkExisting() {
    const guru = document.getElementById('select-guru').value;
    const tgl  = document.getElementById('input-tanggal').value;
    const warning = document.getElementById('warning-overwrite');
    const btnText = document.getElementById('btn-submit-text');

    isOverwrite = false;
    warning.classList.add('hidden');
    btnText.textContent = 'Simpan Absensi';

    if (!guru || !tgl) return;

    try {
        const res = await fetch(CHECK_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ id_guru: guru, tanggal: tgl })
        });

        const data = await res.json();

        if (data.exists) {
            isOverwrite = true;
            warning.classList.remove('hidden');
            const method = data.input_method === 'scan' ? 'via scan QR' : 'input manual';
            const statusLabel = {
                hadir: 'Hadir', izin: 'Izin', sakit: 'Sakit', alpa: 'Alpa'
            }[data.status] ?? data.status;
            document.getElementById('overwrite-info').innerHTML =
                `Status saat ini <strong>${statusLabel}</strong> (${method})`;
            btnText.textContent = 'Simpan & Overwrite';
        }
    } catch (e) {
        console.error('Check existing failed:', e);
    }
}

// Submit handler
function handleSubmit() {
    const guru = document.getElementById('select-guru').value;
    const tgl  = document.getElementById('input-tanggal').value;

    if (!guru) { alert('Pilih guru terlebih dahulu'); return; }
    if (!tgl)  { alert('Pilih tanggal'); return; }

    if (isOverwrite) {
        // Buka modal password confirmation
        openPasswordModal('form-single', 'confirm-password-modal', 'confirm_password');
    } else {
        // Langsung submit
        document.getElementById('form-single').submit();
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    toggleJamMasuk();
    checkExisting(); // Cek saat load (jika ada old() values)
});
document.getElementById('select-status').addEventListener('change', toggleJamMasuk);
document.getElementById('select-guru').addEventListener('change', checkExisting);
document.getElementById('input-tanggal').addEventListener('change', checkExisting);
</script>
@endpush

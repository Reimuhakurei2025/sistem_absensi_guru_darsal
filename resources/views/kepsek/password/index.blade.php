@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Reset Password')
@section('page-title', 'Reset Password')

@section('content')
    {{-- Tab navigasi role --}}
    <div class="flex gap-1 mb-4 bg-white p-1 rounded-lg shadow-card overflow-x-auto">
        @foreach([
            'guru'   => ['label' => 'Guru',           'icon' => 'users'],
            'admin'  => ['label' => 'Admin TU',       'icon' => 'users'],
            'kepsek' => ['label' => 'Kepala Sekolah', 'icon' => 'key'],
        ] as $key => $cfg)
            <a href="{{ route('kepsek.password.index', ['tab' => $key]) }}"
               class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium
                      whitespace-nowrap transition-colors
                      {{ $tab === $key ? 'bg-primary-700 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <x-sidebar-icon :name="$cfg['icon']" class="w-4 h-4" />
                {{ $cfg['label'] }}
            </a>
        @endforeach
    </div>

    {{-- List user --}}
    @if($users->isEmpty())
        <x-empty-state
            icon="users"
            title="Tidak ada user di kategori ini"
            message="Tambahkan user terlebih dahulu sebelum mereset password." />
    @else
        <div class="card p-0 overflow-hidden">
            <div class="divide-y divide-gray-100">
                @foreach($users as $user)
                    <div class="flex items-center gap-3 p-4 hover:bg-gray-50">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center
                                    text-primary-700 font-semibold flex-shrink-0">
                            {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-800 truncate">{{ $user->nama_lengkap }}</div>
                            <div class="text-xs text-gray-500 font-mono">@<span>{{ $user->username }}</span></div>
                        </div>
                        @if(!$user->is_active)
                            <span class="badge badge-alpa hidden sm:inline-flex">Nonaktif</span>
                        @endif
                        <button type="button"
                                onclick="openResetForm({{ $user->id }}, '{{ addslashes($user->nama_lengkap) }}', '{{ $user->role }}')"
                                class="btn-secondary text-xs flex-shrink-0">
                            <x-sidebar-icon name="key" class="w-3.5 h-3.5 mr-1" />
                            Reset
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============== MODAL RESET PASSWORD ============== --}}
    <div id="reset-form-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center px-4
                bg-black/50 backdrop-blur-sm"
         role="dialog" aria-modal="true">

        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">

            <h3 class="text-lg font-semibold text-gray-900 mb-1">Reset Password</h3>
            <p class="text-sm text-gray-600 mb-4">
                Reset password untuk: <strong id="reset-target-name" class="text-gray-800"></strong>
            </p>

            <form id="form-reset-password" method="POST" action="{{ route('kepsek.password.reset') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="user_id" id="reset-user-id">
                <input type="hidden" name="role" id="reset-user-role">

                <div>
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" id="new-password"
                           class="form-input" placeholder="Minimal 6 karakter" minlength="6" required>
                </div>
                <div>
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" id="new-password-confirm"
                           class="form-input" placeholder="Ulangi password baru" minlength="6" required>
                    <p id="password-mismatch" class="text-xs text-red-600 mt-1 hidden">
                        Password tidak cocok
                    </p>
                </div>

                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" onclick="closeResetForm()" class="btn-secondary">
                        Batal
                    </button>
                    <button type="button" onclick="submitResetForm()" class="btn-primary">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal konfirmasi password Kepsek (dipasang setelah modal reset) --}}
    <x-password-confirm-modal />
@endsection

@push('scripts')
<script>
function openResetForm(userId, namaLengkap, role) {
    document.getElementById('reset-user-id').value = userId;
    document.getElementById('reset-user-role').value = role;
    document.getElementById('reset-target-name').textContent = namaLengkap;
    document.getElementById('new-password').value = '';
    document.getElementById('new-password-confirm').value = '';
    document.getElementById('password-mismatch').classList.add('hidden');

    const modal = document.getElementById('reset-form-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeResetForm() {
    const modal = document.getElementById('reset-form-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function submitResetForm() {
    const pwd     = document.getElementById('new-password').value.trim();
    const pwdConf = document.getElementById('new-password-confirm').value.trim();
    const mismatch = document.getElementById('password-mismatch');
    const nama    = document.getElementById('reset-target-name').textContent;

    if (pwd.length < 6) {
        mismatch.textContent = 'Password minimal 6 karakter';
        mismatch.classList.remove('hidden');
        return;
    }

    if (pwd !== pwdConf) {
        mismatch.textContent = 'Konfirmasi password tidak cocok';
        mismatch.classList.remove('hidden');
        return;
    }

    mismatch.classList.add('hidden');

    // Tutup modal reset, buka modal konfirmasi password Kepsek
    closeResetForm();

    document.getElementById('password-confirm-modal-title').textContent = 'Konfirmasi Reset Password';
    document.getElementById('password-confirm-modal-message').textContent =
        `Reset password "${nama}". Masukkan password Anda untuk konfirmasi.`;

    openPasswordModal('form-reset-password');
}
</script>
@endpush

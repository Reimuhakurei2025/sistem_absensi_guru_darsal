@extends('layouts.app', ['role' => 'kepsek', 'currentUser' => auth('kepsek')->user()])

@section('title', 'Ganti Password')
@section('page-title', 'Ganti Password')

@section('content')
    <div class="max-w-3xl">

        {{-- Tab navigasi --}}
        <div class="flex gap-1 mb-4 bg-white p-1 rounded-lg shadow-card">
            <a href="{{ route('kepsek.profil.edit') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium
                      text-gray-600 hover:bg-gray-100">
                <x-sidebar-icon name="user" class="w-4 h-4" />
                Biodata
            </a>
            <a href="{{ route('kepsek.profil.password') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium
                      bg-primary-700 text-white">
                <x-sidebar-icon name="key" class="w-4 h-4" />
                Ganti Password
            </a>
        </div>

        {{-- Info --}}
        <div class="card bg-yellow-50 border-yellow-200 mb-4 text-sm text-yellow-900">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <strong class="font-semibold">Tips Password yang Aman:</strong>
                    <ul class="mt-1 ml-4 list-disc text-xs space-y-0.5">
                        <li>Minimal 6 karakter, disarankan 8+ karakter</li>
                        <li>Kombinasi huruf besar, kecil, angka, dan simbol</li>
                        <li>Jangan gunakan tanggal lahir, nama, atau kata umum</li>
                        <li>Jangan share password ke siapa pun</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Form Ganti Password --}}
        <form method="POST" action="{{ route('kepsek.profil.password.update') }}" class="card space-y-4">
            @csrf
            @method('PUT')

            <h3 class="text-base font-semibold text-gray-800">Ganti Password Anda</h3>

            <div>
                <label class="form-label">Password Lama <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" name="current_password" id="current-password"
                           class="form-input pr-10" placeholder="Password Anda saat ini"
                           required autocomplete="current-password">
                    <button type="button"
                            onclick="togglePwd('current-password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            aria-label="Tampilkan password">
                        <x-sidebar-icon name="eye" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div>
                <label class="form-label">Password Baru <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" name="new_password" id="new-password"
                           class="form-input pr-10" placeholder="Minimal 6 karakter"
                           required minlength="6" autocomplete="new-password">
                    <button type="button"
                            onclick="togglePwd('new-password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <x-sidebar-icon name="eye" class="w-5 h-5" />
                    </button>
                </div>
                {{-- Password strength indicator --}}
                <div class="mt-2 hidden" id="pwd-strength-container">
                    <div class="flex gap-1 h-1">
                        <div id="strength-1" class="flex-1 bg-gray-200 rounded"></div>
                        <div id="strength-2" class="flex-1 bg-gray-200 rounded"></div>
                        <div id="strength-3" class="flex-1 bg-gray-200 rounded"></div>
                        <div id="strength-4" class="flex-1 bg-gray-200 rounded"></div>
                    </div>
                    <p id="strength-text" class="text-xs mt-1 text-gray-500"></p>
                </div>
            </div>

            <div>
                <label class="form-label">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="new_password_confirmation" id="new-password-confirm"
                       class="form-input" placeholder="Ulangi password baru"
                       required minlength="6" autocomplete="new-password">
                <p id="pwd-match" class="text-xs mt-1 hidden"></p>
            </div>

            <div class="flex gap-2 pt-2">
                <a href="{{ route('kepsek.profil.edit') }}" class="btn-secondary flex-1 sm:flex-none">
                    Batal
                </a>
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Ganti Password
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
function togglePwd(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Password strength indicator
function checkStrength(pwd) {
    let score = 0;
    if (pwd.length >= 6) score++;
    if (pwd.length >= 8) score++;
    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
    if (/\d/.test(pwd) && /[^A-Za-z0-9]/.test(pwd)) score++;
    return score;
}

const newPwd = document.getElementById('new-password');
const pwdConfirm = document.getElementById('new-password-confirm');
const strengthContainer = document.getElementById('pwd-strength-container');
const strengthText = document.getElementById('strength-text');

newPwd.addEventListener('input', () => {
    const val = newPwd.value;
    if (!val) {
        strengthContainer.classList.add('hidden');
        return;
    }
    strengthContainer.classList.remove('hidden');

    const score = checkStrength(val);
    const colors = ['bg-red-400', 'bg-yellow-400', 'bg-blue-400', 'bg-green-500'];
    const labels = ['Lemah', 'Cukup', 'Baik', 'Sangat Kuat'];

    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('strength-' + i);
        el.className = 'flex-1 rounded ' + (i <= score ? colors[score - 1] : 'bg-gray-200');
    }
    strengthText.textContent = 'Kekuatan: ' + (labels[score - 1] || 'Sangat Lemah');
});

// Match validation
function checkMatch() {
    const match = document.getElementById('pwd-match');
    if (!pwdConfirm.value) {
        match.classList.add('hidden');
        return;
    }
    if (newPwd.value === pwdConfirm.value) {
        match.textContent = '✓ Password cocok';
        match.className = 'text-xs mt-1 text-green-600';
        match.classList.remove('hidden');
    } else {
        match.textContent = '✗ Password tidak cocok';
        match.className = 'text-xs mt-1 text-red-600';
        match.classList.remove('hidden');
    }
}

pwdConfirm.addEventListener('input', checkMatch);
newPwd.addEventListener('input', checkMatch);
</script>
@endpush

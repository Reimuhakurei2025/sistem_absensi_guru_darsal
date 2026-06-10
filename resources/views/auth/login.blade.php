@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="text-center mb-5">
        <h2 class="text-xl font-semibold text-gray-800">Masuk ke Akun Anda</h2>
        <p class="text-sm text-gray-500 mt-1">
            Gunakan username dan password yang telah diberikan.
        </p>
    </div>

    <form method="POST" action="{{ route('login.process') }}" class="space-y-4">
        @csrf

        <div>
            <label for="username" class="form-label">Username</label>
            <input type="text"
                   name="username"
                   id="username"
                   value="{{ old('username') }}"
                   class="form-input"
                   placeholder="contoh: ahmad.fauzi"
                   autofocus
                   required>
        </div>

        <div>
            <label for="password" class="form-label">Password</label>
            <div class="relative">
                <input type="password"
                       name="password"
                       id="password"
                       class="form-input pr-10"
                       placeholder="••••••••"
                       required>
                <button type="button"
                        onclick="togglePassword()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        aria-label="Tampilkan password">
                    <svg id="icon-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="remember"
                   class="rounded border-gray-300 text-primary-700 focus:ring-primary-400">
            Ingat saya
        </label>

        <button type="submit" class="btn-primary w-full justify-center">
            Masuk
        </button>
    </form>

    <div class="mt-5 pt-5 border-t border-gray-100 text-center">
        <details class="text-xs text-gray-500">
            <summary class="cursor-pointer hover:text-primary-700 select-none">
                Lupa password? <span class="text-primary-700 underline">Klik di sini</span>
            </summary>
            <div class="mt-3 text-left bg-gray-50 rounded-lg p-3 space-y-2">
                <div>
                    <strong class="text-gray-700">Untuk Guru &amp; Admin:</strong>
                    <p class="text-gray-500">Hubungi Kepala Sekolah untuk reset password.</p>
                </div>
                <div>
                    <strong class="text-gray-700">Untuk Kepala Sekolah:</strong>
                    <p class="text-gray-500">
                        Hubungi administrator sistem (IT support) untuk reset password
                        melalui terminal server.
                    </p>
                </div>
            </div>
        </details>
    </div>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush

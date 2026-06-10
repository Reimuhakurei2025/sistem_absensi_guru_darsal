@extends('layouts.app', ['role' => 'admin', 'currentUser' => auth('admin')->user()])

@section('title', 'Ganti Password')
@section('page-title', 'Ganti Password')

@section('content')
    <div class="max-w-3xl">

        <div class="flex gap-1 mb-4 bg-white p-1 rounded-lg shadow-card">
            <a href="{{ route('admin.profil.edit') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium
                      text-gray-600 hover:bg-gray-100">
                <x-sidebar-icon name="user" class="w-4 h-4" />
                Biodata
            </a>
            <a href="{{ route('admin.profil.password') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium
                      bg-primary-700 text-white">
                <x-sidebar-icon name="key" class="w-4 h-4" />
                Ganti Password
            </a>
        </div>

        <form method="POST" action="{{ route('admin.profil.password.update') }}" class="card space-y-4">
            @csrf
            @method('PUT')

            <h3 class="text-base font-semibold text-gray-800">Ganti Password Anda</h3>

            <div>
                <label class="form-label">Password Lama <span class="text-red-500">*</span></label>
                <input type="password" name="current_password"
                       class="form-input" placeholder="Password saat ini"
                       required autocomplete="current-password">
            </div>

            <div>
                <label class="form-label">Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="new_password"
                       class="form-input" placeholder="Minimal 6 karakter"
                       required minlength="6" autocomplete="new-password">
            </div>

            <div>
                <label class="form-label">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="new_password_confirmation"
                       class="form-input" placeholder="Ulangi password baru"
                       required minlength="6" autocomplete="new-password">
            </div>

            <div class="flex gap-2 pt-2">
                <a href="{{ route('admin.profil.edit') }}" class="btn-secondary flex-1 sm:flex-none">Batal</a>
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Ganti Password</button>
            </div>
        </form>
    </div>
@endsection

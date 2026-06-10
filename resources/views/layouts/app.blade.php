<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistem Absensi') · SMP Terpadu Darussalam</title>

    {{-- Logo favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-darussalam.png') }}">

    {{-- Inter font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen">

    <div class="flex min-h-screen">

        {{-- ============ SIDEBAR ============ --}}
        <x-sidebar :role="$role ?? 'guru'" :user="$currentUser ?? auth()->user()" />

        {{-- ============ OVERLAY (mobile only) ============ --}}
        <div id="sidebar-overlay"
             class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden"></div>

        {{-- ============ MAIN CONTENT AREA ============ --}}
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

            {{-- Topbar (mobile: hamburger + title; desktop: judul + user info) --}}
            <header class="bg-white border-b border-gray-200 sticky top-0 z-20">
                <div class="flex items-center justify-between px-4 lg:px-6 h-16">

                    {{-- Hamburger (mobile only) --}}
                    <button id="btn-sidebar-open"
                            class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-gray-100"
                            aria-label="Buka menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Page title --}}
                    <h1 class="flex-1 lg:flex-none text-lg font-semibold text-gray-800 ml-3 lg:ml-0 truncate">
                        @yield('page-title', 'Dashboard')
                    </h1>

                    {{-- User info (compact) --}}
                    <div class="flex items-center gap-3">
                        @auth($role ?? 'guru')
                            <div class="hidden sm:block text-right">
                                <div class="text-sm font-medium text-gray-800 truncate max-w-[160px]">
                                    {{ ($currentUser ?? auth()->user())->nama_lengkap }}
                                </div>
                                <div class="text-xs text-gray-500 capitalize">
                                    {{ $role ?? 'guru' }}
                                </div>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center
                                        text-primary-700 font-semibold">
                                {{ strtoupper(substr(($currentUser ?? auth()->user())->nama_lengkap, 0, 1)) }}
                            </div>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="px-4 lg:px-6 pt-4">
                <x-flash-message />
            </div>

            {{-- Main content --}}
            <main class="flex-1 px-4 lg:px-6 py-4 pb-10">
                @yield('content')
            </main>

            {{-- Footer kecil --}}
            <footer class="text-center text-xs text-gray-400 py-4 border-t border-gray-100">
                © {{ date('Y') }} SMP Terpadu Darussalam · Sistem Absensi Guru
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

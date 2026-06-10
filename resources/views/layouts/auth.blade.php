<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') · SMP Terpadu Darussalam</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-darussalam.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen
             bg-gradient-to-br from-primary-50 via-white to-primary-100">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">

        {{-- Branding header --}}
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo-darussalam.png') }}"
                 alt="Logo SMP Terpadu Darussalam"
                 class="w-20 h-20 mx-auto mb-3 drop-shadow-md">
            <h1 class="text-xl sm:text-2xl font-bold text-primary-800">
                SMP Terpadu Darussalam
            </h1>
            <p class="text-sm text-gray-600 mt-1">Sistem Absensi Guru</p>
        </div>

        {{-- Flash error --}}
        <div class="w-full max-w-md mb-3">
            <x-flash-message />
        </div>

        {{-- Form container --}}
        <div class="w-full max-w-md bg-white rounded-2xl shadow-soft p-6 sm:p-8">
            @yield('content')
        </div>

        <p class="mt-6 text-xs text-gray-400">
            © {{ date('Y') }} SMP Terpadu Darussalam · Bojongsari, Depok
        </p>
    </div>

    @stack('scripts')
</body>
</html>

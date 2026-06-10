<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak') · SMP Terpadu Darussalam</title>

    @vite(['resources/css/app.css'])

    <style>
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .print-area { box-shadow: none !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">

    {{-- Toolbar (tidak ikut tercetak) --}}
    <div class="no-print sticky top-0 bg-white border-b border-gray-200 z-20 px-4 py-3">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button onclick="window.history.back()" class="btn-secondary text-sm">
                    ← Kembali
                </button>
            </div>
            <div class="flex items-center gap-2">
                @yield('toolbar-extra')
                <button onclick="window.print()" class="btn-primary text-sm">
                    🖨️ Cetak
                </button>
            </div>
        </div>
    </div>

    <main class="max-w-4xl mx-auto p-4 sm:p-8">
        @yield('content')
    </main>

</body>
</html>

@props([
    'role' => 'guru',
    'user' => null,
])

@php
    /**
     * Definisi menu berdasarkan role.
     * Setiap menu: ['label', 'icon' (svg path), 'route', 'pattern' (untuk active state)]
     */
    $menus = [
        'kepsek' => [
            ['label' => 'Dashboard',         'icon' => 'home',     'route' => 'kepsek.dashboard',       'pattern' => 'kepsek.dashboard'],
            ['label' => 'Manajemen Guru',    'icon' => 'users',    'route' => 'kepsek.guru.index',      'pattern' => 'kepsek.guru.*'],
            ['label' => 'Input Absensi',     'icon' => 'pencil',   'route' => 'kepsek.absensi-manual.single', 'pattern' => 'kepsek.absensi-manual.*'],
            ['label' => 'Reset Password',    'icon' => 'key',      'route' => 'kepsek.password.index',  'pattern' => 'kepsek.password.*'],
            ['label' => 'Laporan Harian',    'icon' => 'clock',    'route' => 'kepsek.laporan.harian',  'pattern' => 'kepsek.laporan.harian'],
            ['label' => 'Laporan Bulanan',   'icon' => 'document', 'route' => 'kepsek.laporan.bulanan', 'pattern' => 'kepsek.laporan.bulanan'],
            ['label' => 'Ranking Kehadiran', 'icon' => 'trophy',   'route' => 'kepsek.laporan.ranking', 'pattern' => 'kepsek.laporan.ranking'],
            ['label' => 'Profil Saya',       'icon' => 'user',     'route' => 'kepsek.profil.edit',     'pattern' => 'kepsek.profil.*'],
        ],
        'admin' => [
            ['label' => 'Dashboard',       'icon' => 'home',    'route' => 'admin.dashboard',  'pattern' => 'admin.dashboard'],
            ['label' => 'Daftar Guru',     'icon' => 'users',   'route' => 'admin.guru.index', 'pattern' => 'admin.guru.index'],
            ['label' => 'Input Absensi',   'icon' => 'pencil',  'route' => 'admin.absensi-manual.single', 'pattern' => 'admin.absensi-manual.*'],
            ['label' => 'Laporan Harian',  'icon' => 'clock',   'route' => 'admin.laporan.harian', 'pattern' => 'admin.laporan.harian'],
            ['label' => 'Cetak Barcode',   'icon' => 'qrcode',  'route' => 'admin.guru.cetak-semua', 'pattern' => 'admin.guru.cetak-semua'],
            ['label' => 'Profil Saya',     'icon' => 'user',    'route' => 'admin.profil.edit', 'pattern' => 'admin.profil.*'],
        ],
        'guru' => [
            ['label' => 'Dashboard',  'icon' => 'home',    'route' => 'guru.dashboard', 'pattern' => 'guru.dashboard'],
            ['label' => 'Scan Absen', 'icon' => 'qrcode',  'route' => 'guru.scan',      'pattern' => 'guru.scan'],
            ['label' => 'Riwayat',    'icon' => 'clock',   'route' => 'guru.riwayat',   'pattern' => 'guru.riwayat'],
        ],
    ];

    $currentMenu = $menus[$role] ?? $menus['guru'];

    // Label role yang ditampilkan
    $roleLabels = [
        'kepsek' => 'Kepala Sekolah',
        'admin'  => 'Tata Usaha',
        'guru'   => 'Guru',
    ];
    $roleLabel = $roleLabels[$role] ?? 'User';
@endphp

<aside id="sidebar"
       class="fixed top-0 left-0 z-40 w-64 h-screen bg-white border-r border-gray-200
              transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out
              flex flex-col">

    {{-- ========== HEADER SIDEBAR ========== --}}
    <div class="flex items-center gap-3 px-5 h-16 border-b border-gray-100 bg-gradient-to-r
                from-primary-700 to-primary-600">

        <img src="{{ asset('images/logo-darussalam.png') }}"
             alt="Logo"
             class="w-9 h-9 rounded-full bg-white p-0.5 shadow-sm">

        <div class="flex-1 min-w-0">
            <h2 class="text-sm font-semibold text-white leading-tight truncate">
                SMP Darussalam
            </h2>
            <p class="text-xs text-primary-100">Absensi Guru</p>
        </div>

        {{-- Tombol close (mobile only) --}}
        <button id="btn-sidebar-close"
                class="lg:hidden p-1.5 -mr-1.5 rounded text-white/80 hover:text-white hover:bg-white/10"
                aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ========== USER INFO ========== --}}
    @if($user)
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                @if(isset($user->foto) && $user->foto)
                    <img src="{{ asset('storage/' . $user->foto) }}"
                         class="w-10 h-10 rounded-full object-cover" alt="Foto">
                @else
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center
                                text-primary-700 font-semibold">
                        {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-800 truncate">
                        {{ $user->nama_lengkap }}
                    </div>
                    <div class="text-xs text-primary-700 font-medium">
                        {{ $roleLabel }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== MENU ITEMS ========== --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-1">
            @foreach($currentMenu as $menu)
                @php
                    $isActive = request()->routeIs($menu['pattern']);
                @endphp
                <li>
                    <a href="{{ route($menu['route']) }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                              transition-colors duration-150
                              {{ $isActive
                                 ? 'bg-primary-50 text-primary-800'
                                 : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">

                        {{-- Icon --}}
                        <x-sidebar-icon :name="$menu['icon']"
                                        class="w-5 h-5 {{ $isActive ? 'text-primary-700' : 'text-gray-400' }}" />

                        <span>{{ $menu['label'] }}</span>

                        @if($isActive)
                            <span class="ml-auto w-1.5 h-1.5 rounded-full bg-primary-700"></span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- ========== LOGOUT ========== --}}
    <div class="px-3 py-4 border-t border-gray-100">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                           text-red-600 hover:bg-red-50 transition-colors duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

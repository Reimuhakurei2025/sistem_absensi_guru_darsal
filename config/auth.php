<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Default guard adalah 'guru' karena merupakan user paling banyak.
    | Login di setiap role akan eksplisit memanggil guard masing-masing.
    |
    */

    'defaults' => [
        'guard'     => 'guru',
        'passwords' => 'gurus',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Sistem absensi guru SMP Terpadu Darussalam menggunakan 3 guard:
    | - guru   : akses dashboard guru + scan barcode
    | - admin  : akses manajemen guru + lihat barcode
    | - kepsek : akses penuh (super admin)
    |
    */

    'guards' => [
        'guru' => [
            'driver'   => 'session',
            'provider' => 'gurus',
        ],

        'admin' => [
            'driver'   => 'session',
            'provider' => 'admins',
        ],

        'kepsek' => [
            'driver'   => 'session',
            'provider' => 'kepseks',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'gurus' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Guru::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Admin::class,
        ],

        'kepseks' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Kepsek::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords (tidak dipakai aktif - reset password via Kepsek)
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'gurus' => [
            'provider' => 'gurus',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];

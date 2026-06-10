<?php

/**
 * Konfigurasi Dompdf untuk project Sistem Absensi Darussalam.
 *
 * Settings utama:
 *  - enable_remote: true → izinkan load resource lokal (logo, dll)
 *  - enable_html5_parser: true → parser HTML modern
 *  - enable_php: false → keamanan, jangan eval PHP di template PDF
 *  - chroot: dibatasi ke public_path() agar tidak akses file di luar
 *
 * SVG rendering: Dompdf 3.x mendukung SVG sederhana (path, rect)
 * yang cukup untuk render QR Code dari simple-qrcode.
 */

return [
    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => true,

    'options' => [
        // Folder font (default sudah cukup)
        'font_dir' => storage_path('fonts/'),
        'font_cache' => storage_path('fonts/'),
        'temp_dir' => sys_get_temp_dir(),

        // Akses file lokal untuk logo & image
        'chroot' => realpath(base_path()),

        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        // Default font
        'default_font' => 'DejaVu Sans',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',

        // Default media type
        'default_media_type' => 'print',

        // DPI - 96 standar untuk web
        'dpi' => 96,

        // Pengaturan font subsetting (mengurangi ukuran PDF)
        'enable_font_subsetting' => false,

        // Encoding & parser
        'pdf_backend' => 'CPDF',
        'enable_html5_parser' => true,
        'enable_javascript' => false,

        // PENTING: untuk render SVG (QR Code)
        'enable_remote' => true,
        'enable_css_float' => true,

        // Keamanan: jangan biarkan eval PHP
        'enable_php' => false,

        // Debug (set false di production)
        'debug_png' => false,
        'debug_keep_temp' => false,
        'debug_css' => false,
        'debug_layout' => false,
        'debug_layout_lines' => true,
        'debug_layout_blocks' => true,
        'debug_layout_inline' => true,
        'debug_layout_padding_box' => true,

        'pdfa' => false,
    ],
];

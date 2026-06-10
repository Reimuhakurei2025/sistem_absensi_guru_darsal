<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * QrCodeService — Pembungkus generate QR dengan auto-fallback.
 *
 * Strategi:
 *  1. Untuk WEB (browser): selalu pakai SVG (ringan, vector, browser support 100%)
 *  2. Untuk PDF (dompdf):
 *      a. Coba SVG dulu (kalau dompdf bisa render)
 *      b. Kalau gagal/blank → pakai PNG via GD (bawaan PHP)
 *      c. Kalau GD tidak ada → tampilkan placeholder text
 *
 * Method utama:
 *  - forWeb($token, $size)  : return SVG string
 *  - forPdf($token, $size)  : return data URI (base64 PNG kalau GD tersedia,
 *                              atau base64 SVG sebagai fallback)
 *
 * Cara pakai di Blade PDF:
 *   <img src="{{ $qrDataUri }}" style="width: 220px; height: 220px;">
 *
 * Cara pakai di Blade Web:
 *   {!! $qrSvg !!}
 */
class QrCodeService
{
    /**
     * Generate QR Code untuk tampilan web (SVG embedded).
     */
    public static function forWeb(string $token, int $size = 250): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($token);
    }

    /**
     * Generate QR Code untuk PDF dompdf.
     *
     * Return: data URI string yang bisa langsung dipakai di <img src="...">
     *
     * Prioritas format:
     *  1. PNG via GD     → paling reliable di Dompdf
     *  2. SVG base64     → fallback kalau GD tidak ada (lebih jarang)
     */
    public static function forPdf(string $token, int $size = 250): string
    {
        // Cek apakah GD tersedia
        if (function_exists('imagecreate') && extension_loaded('gd')) {
            try {
                $png = QrCode::format('png')
                    ->size($size)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($token);

                return 'data:image/png;base64,' . base64_encode($png);
            } catch (\Throwable $e) {
                // Fall through ke SVG fallback
            }
        }

        // Fallback: SVG base64 (jarang dibutuhkan, GD biasanya selalu ada)
        $svg = QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($token);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Cek apakah environment punya GD extension.
     * Berguna untuk display warning di admin kalau perlu.
     */
    public static function hasGd(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreate');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin GuruController - Read-only access ke daftar guru + cetak barcode.
 *
 * QR Code generation:
 *  - Tampilan web (browser)   : SVG (vector, ringan)
 *  - Untuk PDF (Dompdf)       : PNG via GD (paling reliable di Dompdf 3.x)
 *
 * GD extension biasanya sudah aktif by default di hampir semua hosting PHP
 * sehingga tidak perlu install package atau extension tambahan.
 */
class GuruController extends Controller
{
    /**
     * Daftar semua guru aktif.
     */
    public function index(Request $request): View
    {
        $query = Guru::aktif();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('nama_lengkap', 'like', "%{$q}%")
                   ->orWhere('nip', 'like', "%{$q}%")
                   ->orWhere('mata_pelajaran', 'like', "%{$q}%");
            });
        }

        $gurus = $query->orderBy('nama_lengkap')->paginate(15)->withQueryString();

        return view('admin.guru.index', compact('gurus'));
    }

    /**
     * Tampilkan QR code untuk 1 guru di browser (HTML printable, SVG).
     */
    public function showBarcode(Guru $guru): View
    {
        $qrCode = QrCodeService::forWeb($guru->barcode_token, 280);
        return view('admin.guru.barcode', compact('guru', 'qrCode'));
    }

    /**
     * Halaman cetak semua barcode di browser (HTML grid, SVG).
     */
    public function cetakSemuaBarcode(): View
    {
        $gurus = Guru::aktif()->orderBy('nama_lengkap')->get();

        $qrCodes = $gurus->mapWithKeys(function ($guru) {
            return [$guru->id_guru => QrCodeService::forWeb($guru->barcode_token, 180)];
        });

        return view('admin.guru.cetak-semua', compact('gurus', 'qrCodes'));
    }

    // ============================================================
    // PDF DOWNLOAD
    // ============================================================

    /**
     * Download PDF berisi 1 kartu barcode guru.
     * Pakai PNG via GD untuk reliability di Dompdf.
     */
    public function downloadBarcodePdf(Guru $guru)
    {
        // Data URI - PNG base64 dari GD, atau SVG base64 sebagai fallback
        $qrDataUri = QrCodeService::forPdf($guru->barcode_token, 400);

        $pdf = Pdf::loadView('admin.guru.barcode-pdf', [
            'guru'      => $guru,
            'qrDataUri' => $qrDataUri,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('chroot', public_path());

        $namaFile = 'Kartu_Barcode_' . str_replace(' ', '_', $guru->nama_lengkap) . '.pdf';
        return $pdf->download($namaFile);
    }

    /**
     * Download PDF berisi semua barcode guru (grid 2 kolom).
     */
    public function downloadSemuaPdf()
    {
        $gurus = Guru::aktif()->orderBy('nama_lengkap')->get();

        $qrCodes = $gurus->mapWithKeys(function ($guru) {
            return [$guru->id_guru => QrCodeService::forPdf($guru->barcode_token, 250)];
        });

        $pdf = Pdf::loadView('admin.guru.cetak-semua-pdf', compact('gurus', 'qrCodes'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('chroot', public_path());

        $namaFile = 'Kartu_Barcode_Semua_Guru_' . date('Y-m-d') . '.pdf';
        return $pdf->download($namaFile);
    }
}

@extends('layouts.app', ['role' => 'guru', 'currentUser' => auth('guru')->user()])

@section('title', 'Scan Absensi')
@section('page-title', 'Scan Absensi')

@push('styles')
<style>
    /* Hide UI bawaan html5-qrcode yang tidak perlu */
    #qr-reader__dashboard_section_csr button { display: none !important; }
    #qr-reader__dashboard_section_swaplink { display: none !important; }
    #qr-reader__camera_selection { display: none !important; }
    #qr-reader__status_span { display: none !important; }
    #qr-reader__header_message { display: none !important; }

    #qr-reader {
        border: none !important;
        background: #000;
    }

    #qr-reader video {
        width: 100% !important;
        height: auto !important;
        max-height: 60vh !important;
        object-fit: cover !important;
        border-radius: 0.75rem;
    }

    /* Frame scan custom */
    .scan-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70%;
        max-width: 280px;
        aspect-ratio: 1;
        border: 3px solid #66BB6A;
        border-radius: 1rem;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);
        pointer-events: none;
    }

    .scan-frame::before,
    .scan-frame::after,
    .scan-frame .corner {
        content: '';
        position: absolute;
        width: 24px;
        height: 24px;
        border: 4px solid #fff;
    }
    .scan-frame::before  { top: -3px; left: -3px;  border-right: none; border-bottom: none; border-radius: 8px 0 0 0; }
    .scan-frame::after   { top: -3px; right: -3px; border-left: none;  border-bottom: none; border-radius: 0 8px 0 0; }
    .scan-frame .corner-bl { bottom: -3px; left: -3px;  border-right: none; border-top: none; border-radius: 0 0 0 8px; }
    .scan-frame .corner-br { bottom: -3px; right: -3px; border-left: none;  border-top: none; border-radius: 0 0 8px 0; }

    /* Garis scan animasi */
    @keyframes scan-line {
        0%   { top: 0%; }
        100% { top: 100%; }
    }
    .scan-line {
        position: absolute;
        left: 5%;
        right: 5%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #66BB6A, transparent);
        animation: scan-line 2s ease-in-out infinite alternate;
    }
</style>
@endpush

@section('content')
    <div class="max-w-2xl mx-auto">

        {{-- Info guru yang sedang login --}}
        <div class="card mb-4 flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center
                        text-primary-700 font-semibold flex-shrink-0">
                {{ strtoupper(substr(auth('guru')->user()->nama_lengkap, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-800 truncate">
                    {{ auth('guru')->user()->nama_lengkap }}
                </div>
                <div class="text-xs text-gray-500" id="current-time">
                    {{ now()->translatedFormat('l, d F Y · H:i') }} WIB
                </div>
            </div>
        </div>

        {{-- ============== SCANNER VIEWPORT ============== --}}
        <div id="scanner-container" class="card p-2 sm:p-3 bg-black overflow-hidden">
            <div class="relative rounded-xl overflow-hidden">
                <div id="qr-reader" style="width: 100%;"></div>

                {{-- Overlay frame scan --}}
                <div class="scan-frame">
                    <span class="corner corner-bl"></span>
                    <span class="corner corner-br"></span>
                    <div class="scan-line"></div>
                </div>
            </div>

            <p class="text-center text-xs text-white/80 mt-2">
                Arahkan kamera ke barcode (QR Code) Anda
            </p>
        </div>

        {{-- ============== STATUS / FEEDBACK ============== --}}
        <div id="status-area" class="mt-4">
            <div id="status-idle" class="card text-center py-4">
                <p class="text-sm text-gray-600">
                    📷 <strong>Menunggu scan...</strong>
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Pastikan barcode terlihat jelas dalam kotak hijau
                </p>
            </div>

            {{-- Loading state --}}
            <div id="status-loading" class="card text-center py-6 hidden">
                <div class="inline-block w-8 h-8 border-[3px] border-primary-200 border-t-primary-700
                            rounded-full animate-spin"></div>
                <p class="text-sm text-gray-700 mt-2 font-medium">Memproses...</p>
            </div>

            {{-- Success state --}}
            <div id="status-success"
                 class="card border-green-200 bg-gradient-to-br from-green-50 to-white text-center py-6 hidden">
                <div class="w-16 h-16 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-3">
                    <svg class="w-9 h-9 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-green-800">Absensi Berhasil!</h3>
                <p id="success-message" class="text-sm text-green-700 mt-1">Selamat bekerja</p>
                <div id="success-detail" class="mt-3 text-xs text-gray-600"></div>
                <a href="{{ route('guru.dashboard') }}" class="btn-primary mt-4 inline-flex">
                    Kembali ke Dashboard
                </a>
            </div>

            {{-- Error state --}}
            <div id="status-error"
                 class="card border-red-200 bg-red-50 text-center py-6 hidden">
                <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-3">
                    <svg class="w-9 h-9 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-red-800">Gagal</h3>
                <p id="error-message" class="text-sm text-red-700 mt-1">Terjadi kesalahan</p>
                <button onclick="resetScanner()" class="btn-secondary mt-4">
                    Coba Lagi
                </button>
            </div>
        </div>

        {{-- Tips --}}
        <div class="mt-4 p-4 bg-primary-50 border border-primary-100 rounded-lg text-xs text-primary-800">
            <strong class="font-semibold">💡 Tips:</strong>
            <ul class="mt-1 ml-5 list-disc space-y-0.5">
                <li>Pastikan pencahayaan cukup</li>
                <li>Pegang barcode dengan stabil</li>
                <li>Jarak ideal 15-25 cm dari kamera</li>
                <li>Browser akan meminta izin kamera saat pertama kali</li>
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
{{-- html5-qrcode dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let isProcessing = false;

const SCAN_URL = "{{ route('guru.scan.process') }}";
const CSRF     = "{{ csrf_token() }}";

// ====== STATE MANAGEMENT ======
function showStatus(state) {
    ['idle', 'loading', 'success', 'error'].forEach(s => {
        document.getElementById('status-' + s).classList.add('hidden');
    });
    document.getElementById('status-' + state).classList.remove('hidden');
}

// ====== START SCANNER ======
async function startScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");

    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
    };

    try {
        // Coba kamera belakang dulu (untuk HP)
        await html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        );
    } catch (err) {
        // Fallback: pakai kamera apapun yang tersedia (laptop biasanya kamera depan)
        try {
            const cameras = await Html5Qrcode.getCameras();
            if (cameras && cameras.length) {
                await html5QrCode.start(
                    cameras[0].id,
                    config,
                    onScanSuccess,
                    onScanFailure
                );
            } else {
                throw new Error('Tidak ada kamera ditemukan');
            }
        } catch (fallbackErr) {
            showStatus('error');
            document.getElementById('error-message').textContent =
                'Tidak dapat mengakses kamera. Pastikan browser memiliki izin kamera.';
            console.error(fallbackErr);
        }
    }
}

// ====== ON SCAN SUCCESS ======
async function onScanSuccess(decodedText) {
    if (isProcessing) return;
    isProcessing = true;

    // Pause kamera saat memproses (agar tidak scan berulang)
    if (html5QrCode) {
        try { await html5QrCode.pause(true); } catch (_) {}
    }

    showStatus('loading');

    try {
        const response = await fetch(SCAN_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ token: decodedText.trim() })
        });

        const data = await response.json();

        if (response.ok && data.status === 'success') {
            showStatus('success');
            document.getElementById('success-message').textContent = data.message;
            if (data.data) {
                document.getElementById('success-detail').innerHTML =
                    `<strong>${data.data.nama}</strong><br>` +
                    `${data.data.tanggal} · ${data.data.jam_masuk} WIB`;
            }

            // Stop kamera permanen
            if (html5QrCode) {
                try { await html5QrCode.stop(); } catch (_) {}
            }
        } else {
            showStatus('error');
            document.getElementById('error-message').textContent =
                data.message || 'Validasi barcode gagal';
        }
    } catch (err) {
        console.error(err);
        showStatus('error');
        document.getElementById('error-message').textContent =
            'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
    }
}

function onScanFailure(error) {
    // Dipanggil setiap frame saat tidak ada QR di kamera — diabaikan saja
}

// ====== RESET SCANNER ======
async function resetScanner() {
    isProcessing = false;
    showStatus('idle');

    if (html5QrCode) {
        try {
            await html5QrCode.resume();
        } catch (_) {
            await startScanner();
        }
    } else {
        await startScanner();
    }
}

// ====== UPDATE JAM REAL-TIME ======
function updateClock() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');

    document.getElementById('current-time').textContent =
        `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} · ${hh}:${mm}:${ss} WIB`;
}

document.addEventListener('DOMContentLoaded', () => {
    startScanner();
    updateClock();
    setInterval(updateClock, 1000);
});

window.addEventListener('beforeunload', () => {
    if (html5QrCode) {
        try { html5QrCode.stop(); } catch (_) {}
    }
});
</script>
@endpush

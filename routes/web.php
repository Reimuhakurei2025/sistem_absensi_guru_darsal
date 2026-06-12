<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Kepsek\DashboardController as KepsekDashboard;
use App\Http\Controllers\Kepsek\GuruController       as KepsekGuru;
use App\Http\Controllers\Kepsek\PasswordController   as KepsekPassword;
use App\Http\Controllers\Kepsek\LaporanController    as KepsekLaporan;
use App\Http\Controllers\Kepsek\ProfilController     as KepsekProfil;
use App\Http\Controllers\Admin\DashboardController   as AdminDashboard;
use App\Http\Controllers\Admin\GuruController        as AdminGuru;
use App\Http\Controllers\Admin\ProfilController      as AdminProfil;
use App\Http\Controllers\Guru\DashboardController    as GuruDashboard;
use App\Http\Controllers\Guru\AbsensiController      as GuruAbsensi;
use App\Http\Controllers\Shared\AbsensiManualController as AbsensiManual;
use App\Http\Controllers\Shared\LaporanHarianController as LaporanHarian;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Sistem Absensi Guru SMP Terpadu Darussalam
| Pembagian akses berdasarkan role: kepsek, admin, guru
|--------------------------------------------------------------------------
*/

// ============================================================
// PUBLIC — Login Page
// ============================================================
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================
// KEPALA SEKOLAH (Super Admin)
// ============================================================
Route::middleware('role:kepsek')->prefix('kepsek')->name('kepsek.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [KepsekDashboard::class, 'index'])->name('dashboard');

    // Manajemen Guru
    Route::prefix('guru')->name('guru.')->group(function () {
        Route::get('/',                [KepsekGuru::class, 'index'])->name('index');
        Route::get('/tambah',          [KepsekGuru::class, 'create'])->name('create');
        Route::post('/',               [KepsekGuru::class, 'store'])->name('store');
        Route::get('/{guru}',          [KepsekGuru::class, 'show'])->name('show');
        Route::get('/{guru}/edit',     [KepsekGuru::class, 'edit'])->name('edit');
        Route::put('/{guru}',          [KepsekGuru::class, 'update'])->name('update');
        Route::post('/{guru}/deactivate', [KepsekGuru::class, 'deactivate'])->name('deactivate');
        Route::post('/{guru}/activate',   [KepsekGuru::class, 'activate'])->name('activate');
    });

    // Reset Password (semua user)
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/',      [KepsekPassword::class, 'index'])->name('index');
        Route::post('/reset', [KepsekPassword::class, 'reset'])->name('reset');
    });

    // Profil Kepsek (edit biodata diri sendiri)
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/',                  [KepsekProfil::class, 'edit'])->name('edit');
        Route::put('/',                  [KepsekProfil::class, 'update'])->name('update');
        Route::get('/password',          [KepsekProfil::class, 'changePasswordForm'])->name('password');
        Route::put('/password',          [KepsekProfil::class, 'changePassword'])->name('password.update');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/harian',          [LaporanHarian::class, 'index'])->name('harian');
        Route::get('/harian/pdf',      [LaporanHarian::class, 'exportPdf'])->name('harian.pdf');
        Route::get('/harian/excel',    [LaporanHarian::class, 'exportExcel'])->name('harian.excel');
        Route::get('/harian/word',     [LaporanHarian::class, 'exportWord'])->name('harian.word');
        Route::get('/bulanan',         [KepsekLaporan::class, 'bulanan'])->name('bulanan');
        Route::get('/bulanan/pdf',     [KepsekLaporan::class, 'bulananPdf'])->name('bulanan.pdf');
        Route::get('/bulanan/excel',   [KepsekLaporan::class, 'bulananExcel'])->name('bulanan.excel');
        Route::get('/bulanan/word',    [KepsekLaporan::class, 'bulananWord'])->name('bulanan.word');
        Route::get('/ranking',         [KepsekLaporan::class, 'ranking'])->name('ranking');
    });

    // Input Absensi Manual (Kepsek)
    Route::prefix('absensi-manual')->name('absensi-manual.')->group(function () {
        Route::get('/single',  [AbsensiManual::class, 'createSingle'])->name('single');
        Route::post('/single', [AbsensiManual::class, 'storeSingle'])->name('single.store');
        Route::get('/bulk',    [AbsensiManual::class, 'createBulk'])->name('bulk');
        Route::post('/bulk',   [AbsensiManual::class, 'storeBulk'])->name('bulk.store');
        Route::get('/riwayat', [AbsensiManual::class, 'riwayatManual'])->name('riwayat');
        Route::post('/check',  [AbsensiManual::class, 'checkExisting'])->name('check');
    });
});

// ============================================================
// ADMIN (Tata Usaha)
// ============================================================
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Daftar Guru (read-only) + Barcode
    Route::prefix('guru')->name('guru.')->group(function () {
        Route::get('/',                       [AdminGuru::class, 'index'])->name('index');
        Route::get('/cetak-semua',            [AdminGuru::class, 'cetakSemuaBarcode'])->name('cetak-semua');
        Route::get('/cetak-semua/pdf',        [AdminGuru::class, 'downloadSemuaPdf'])->name('cetak-semua.pdf');
        Route::get('/{guru}/barcode',         [AdminGuru::class, 'showBarcode'])->name('barcode');
        Route::get('/{guru}/barcode/pdf',     [AdminGuru::class, 'downloadBarcodePdf'])->name('barcode.pdf');
    });

    // Input Absensi Manual (Admin)
    Route::prefix('absensi-manual')->name('absensi-manual.')->group(function () {
        Route::get('/single',  [AbsensiManual::class, 'createSingle'])->name('single');
        Route::post('/single', [AbsensiManual::class, 'storeSingle'])->name('single.store');
        Route::get('/bulk',    [AbsensiManual::class, 'createBulk'])->name('bulk');
        Route::post('/bulk',   [AbsensiManual::class, 'storeBulk'])->name('bulk.store');
        Route::get('/riwayat', [AbsensiManual::class, 'riwayatManual'])->name('riwayat');
        Route::post('/check',  [AbsensiManual::class, 'checkExisting'])->name('check');
    });

    // Laporan Harian (Admin)
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/harian',          [LaporanHarian::class, 'index'])->name('harian');
        Route::get('/harian/pdf',      [LaporanHarian::class, 'exportPdf'])->name('harian.pdf');
        Route::get('/harian/excel',    [LaporanHarian::class, 'exportExcel'])->name('harian.excel');
        Route::get('/harian/word',     [LaporanHarian::class, 'exportWord'])->name('harian.word');
    });

    // Profil Admin
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/',         [AdminProfil::class, 'edit'])->name('edit');
        Route::put('/',         [AdminProfil::class, 'update'])->name('update');
        Route::get('/password', [AdminProfil::class, 'changePasswordForm'])->name('password');
        Route::put('/password', [AdminProfil::class, 'changePassword'])->name('password.update');
    });
});

// ============================================================
// GURU (User akhir)
// ============================================================
Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {

    Route::get('/dashboard', [GuruDashboard::class, 'index'])->name('dashboard');
    Route::get('/riwayat',   [GuruDashboard::class, 'riwayat'])->name('riwayat');

    // Scan QR Code
    Route::get('/scan',      [GuruAbsensi::class, 'scan'])->name('scan');
    Route::post('/scan',     [GuruAbsensi::class, 'processScan'])->name('scan.process');
});

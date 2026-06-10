<?php

namespace App\Console\Commands;

use App\Models\Kepsek;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command: kepsek:reset-password
 *
 * Reset password Kepala Sekolah via CLI.
 * Hanya bisa dijalankan oleh administrator sistem yang memiliki
 * akses langsung ke server (SSH/terminal).
 *
 * Cara pakai:
 *   php artisan kepsek:reset-password              (interaktif)
 *   php artisan kepsek:reset-password kepsek       (langsung tentukan username)
 */
class ResetKepsekPassword extends Command
{
    /**
     * Signature command.
     * {username?} = optional argument; kalau tidak diisi, akan ditanyakan.
     */
    protected $signature = 'kepsek:reset-password
                            {username? : Username Kepala Sekolah yang akan di-reset}';

    /**
     * Deskripsi yang muncul di list `php artisan`.
     */
    protected $description = 'Reset password akun Kepala Sekolah (super admin)';

    /**
     * Eksekusi command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════╗');
        $this->info('║   RESET PASSWORD KEPALA SEKOLAH                    ║');
        $this->info('║   SMP Terpadu Darussalam — Sistem Absensi Guru     ║');
        $this->info('╚════════════════════════════════════════════════════╝');
        $this->newLine();

        // ============================================================
        // STEP 1: Tampilkan daftar Kepsek yang ada
        // ============================================================
        $allKepsek = Kepsek::orderBy('nama_lengkap')->get();

        if ($allKepsek->isEmpty()) {
            $this->error('❌ Tidak ada akun Kepala Sekolah di database.');
            $this->line('   Silakan jalankan: php artisan db:seed');
            return self::FAILURE;
        }

        $this->line('Daftar Kepala Sekolah saat ini:');
        $this->newLine();

        $tableData = $allKepsek->map(fn($k) => [
            'ID'       => $k->id_kepsek,
            'Username' => $k->username,
            'Nama'     => $k->nama_lengkap,
            'Status'   => $k->is_active ? '✓ Aktif' : '✗ Nonaktif',
        ])->toArray();

        $this->table(['ID', 'Username', 'Nama', 'Status'], $tableData);

        // ============================================================
        // STEP 2: Tentukan username target
        // ============================================================
        $username = $this->argument('username');

        if (!$username) {
            $username = $this->ask('Masukkan username Kepala Sekolah yang akan di-reset');
        }

        $username = trim($username);

        if (empty($username)) {
            $this->error('❌ Username tidak boleh kosong.');
            return self::FAILURE;
        }

        // ============================================================
        // STEP 3: Cari user
        // ============================================================
        $kepsek = Kepsek::where('username', $username)->first();

        if (!$kepsek) {
            $this->error("❌ Tidak ditemukan Kepala Sekolah dengan username: '{$username}'");
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Akun yang akan di-reset:');
        $this->line("  • Nama     : <fg=yellow>{$kepsek->nama_lengkap}</>");
        $this->line("  • Username : <fg=yellow>{$kepsek->username}</>");
        $this->line("  • Email    : <fg=yellow>{$kepsek->email}</>");
        $this->line('  • Status   : ' . ($kepsek->is_active ? '<fg=green>Aktif</>' : '<fg=red>Nonaktif</>'));
        $this->newLine();

        // ============================================================
        // STEP 4: Konfirmasi
        // ============================================================
        if (!$this->confirm('Apakah Anda yakin ingin reset password akun ini?', false)) {
            $this->warn('Dibatalkan oleh pengguna.');
            return self::SUCCESS;
        }

        // ============================================================
        // STEP 5: Input password baru (hidden, tanpa echo)
        // ============================================================
        $this->newLine();
        $this->line('💡 Password yang Anda ketik tidak akan terlihat di layar.');
        $this->line('   Minimum 6 karakter. Disarankan kombinasi huruf, angka, simbol.');
        $this->newLine();

        $password = $this->secret('Masukkan password BARU');

        if (strlen($password) < 6) {
            $this->error('❌ Password minimal 6 karakter.');
            return self::FAILURE;
        }

        $passwordConfirm = $this->secret('Ulangi password BARU untuk konfirmasi');

        if ($password !== $passwordConfirm) {
            $this->error('❌ Konfirmasi password tidak cocok.');
            return self::FAILURE;
        }

        // ============================================================
        // STEP 6: Eksekusi reset
        // ============================================================
        $kepsek->update(['password' => $password]); // auto-hashed via $casts

        // Log audit ke storage/logs/laravel.log
        Log::warning('[SECURITY] Kepsek password reset via CLI', [
            'kepsek_id'   => $kepsek->id_kepsek,
            'username'    => $kepsek->username,
            'reset_at'    => now()->toDateTimeString(),
            'reset_by'    => 'Artisan CLI',
            'server_user' => trim(shell_exec('whoami') ?? 'unknown'),
        ]);

        $this->newLine();
        $this->info('✅ Password berhasil di-reset!');
        $this->newLine();
        $this->line('Detail:');
        $this->line("  • Akun       : <fg=yellow>{$kepsek->nama_lengkap}</>");
        $this->line("  • Username   : <fg=yellow>{$kepsek->username}</>");
        $this->line('  • Reset pada : <fg=yellow>' . now()->translatedFormat('l, d F Y · H:i:s') . '</>');
        $this->newLine();
        $this->warn('⚠️  Beritahu Kepala Sekolah untuk segera login dan ganti password jika perlu.');
        $this->warn('⚠️  Aksi ini telah dicatat di file log: storage/logs/laravel.log');
        $this->newLine();

        return self::SUCCESS;
    }
}

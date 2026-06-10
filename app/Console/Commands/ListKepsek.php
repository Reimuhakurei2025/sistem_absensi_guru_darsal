<?php

namespace App\Console\Commands;

use App\Models\Kepsek;
use Illuminate\Console\Command;

/**
 * Artisan Command: kepsek:list
 *
 * Tampilkan daftar semua akun Kepala Sekolah di database.
 * Berguna untuk troubleshooting dan verifikasi akun.
 *
 * Cara pakai:
 *   php artisan kepsek:list
 */
class ListKepsek extends Command
{
    protected $signature = 'kepsek:list';
    protected $description = 'Tampilkan daftar akun Kepala Sekolah';

    public function handle(): int
    {
        $allKepsek = Kepsek::orderBy('nama_lengkap')->get();

        if ($allKepsek->isEmpty()) {
            $this->warn('Tidak ada akun Kepala Sekolah di database.');
            $this->line('Jalankan: php artisan db:seed');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Daftar Kepala Sekolah — Total: ' . $allKepsek->count());
        $this->newLine();

        $this->table(
            ['ID', 'Username', 'Nama Lengkap', 'Email', 'Status', 'Dibuat'],
            $allKepsek->map(fn($k) => [
                $k->id_kepsek,
                $k->username,
                $k->nama_lengkap,
                $k->email,
                $k->is_active ? '✓ Aktif' : '✗ Nonaktif',
                $k->created_at?->format('d M Y') ?? '-',
            ])->toArray()
        );

        $this->newLine();
        $this->line('Untuk reset password: <fg=yellow>php artisan kepsek:reset-password</>');
        $this->newLine();

        return self::SUCCESS;
    }
}

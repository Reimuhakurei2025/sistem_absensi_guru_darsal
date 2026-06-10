<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Guru extends Authenticatable
{
    use Notifiable;

    protected $table      = 'tb_guru';
    protected $primaryKey = 'id_guru';

    protected $fillable = [
        'nip',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tgl_lahir',
        'agama',
        'alamat',
        'no_hp',
        'email',
        'mata_pelajaran',
        'jabatan',
        'username',
        'password',
        'foto',
        'barcode_token',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tgl_lahir'         => 'date',
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * Generate token unik untuk barcode guru.
     * Dipanggil otomatis saat guru dibuat.
     */
    public static function generateBarcodeToken(): string
    {
        do {
            // Format: GR-{8 char random uppercase}
            $token = 'GR-' . strtoupper(Str::random(8));
        } while (self::where('barcode_token', $token)->exists());

        return $token;
    }

    /**
     * Relasi: 1 guru memiliki banyak record absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_guru', 'id_guru');
    }

    /**
     * Scope: hanya guru yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper: cek apakah sudah absen hari ini
     */
    public function sudahAbsenHariIni(): bool
    {
        return $this->absensi()
                    ->whereDate('tanggal', today())
                    ->exists();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table      = 'tb_absensi';
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'id_guru',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
        'keterangan',
        // Audit trail untuk input manual
        'input_method',   // 'scan' atau 'manual'
        'input_by_role',  // 'kepsek', 'admin', null (kalau scan sendiri)
        'input_by_id',    // id_kepsek atau id_admin
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi: setiap absensi dimiliki oleh 1 guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    /**
     * Scope: filter berdasarkan bulan tertentu
     */
    public function scopeBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)
                     ->whereYear('tanggal', $tahun);
    }

    /**
     * Scope: hanya status hadir
     */
    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    /**
     * Helper: cek apakah absensi diinput manual (bukan dari scan)
     */
    public function isManualInput(): bool
    {
        return $this->input_method === 'manual';
    }

    /**
     * Helper: dapatkan nama yang menginput manual
     */
    public function getInputByName(): ?string
    {
        if (!$this->isManualInput() || !$this->input_by_id) {
            return null;
        }

        return match ($this->input_by_role) {
            'kepsek' => Kepsek::find($this->input_by_id)?->nama_lengkap,
            'admin'  => Admin::find($this->input_by_id)?->nama_lengkap,
            default  => null,
        };
    }
}

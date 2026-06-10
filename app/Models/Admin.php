<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table      = 'tb_admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'email',
        'no_hp',
        'foto',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password'  => 'hashed',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }
}

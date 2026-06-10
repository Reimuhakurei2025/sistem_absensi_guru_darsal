<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Kepsek extends Authenticatable
{
    use Notifiable;

    protected $table      = 'tb_kepsek';
    protected $primaryKey = 'id_kepsek';

    protected $fillable = [
        'nip',
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
}

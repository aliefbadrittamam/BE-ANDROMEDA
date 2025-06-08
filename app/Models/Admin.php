<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'admin';
    
    protected $fillable = [
        'username', 'password', 'email', 'nama_lengkap', 'foto_profile'
    ];

    protected $hidden = ['password'];

    public function materi()
    {
        return $this->hasMany(Materi::class);
    }

    public function soal()
    {
        return $this->hasMany(Soal::class);
    }

    public function kuis()
    {
        return $this->hasMany(Kuis::class);
    }
}
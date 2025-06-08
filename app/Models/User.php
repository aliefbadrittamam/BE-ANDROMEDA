<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    
    protected $fillable = [
        'username', 'password', 'email', 'nama_lengkap', 'foto_profile', 'status'
    ];

    protected $hidden = ['password'];

    public function jawaban()
    {
        return $this->hasMany(UserJawaban::class);
    }

    public function hasilKuis()
    {
        return $this->hasMany(HasilKuis::class);
    }

    public function activityLog()
    {
        return $this->hasMany(UserActivityLog::class);
    }
}
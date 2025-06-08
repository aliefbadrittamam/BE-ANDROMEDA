<?php
namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    
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
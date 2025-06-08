<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $table = 'kuis';
    
    protected $fillable = [
        'nama_kuis', 'deskripsi', 'deadline', 'durasi_menit', 'status', 'admin_id'
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function soal()
    {
        return $this->belongsToMany(Soal::class, 'kuis_soal')->withPivot('urutan')->withTimestamps()->orderBy('urutan');
    }

    public function userJawaban()
    {
        return $this->hasMany(UserJawaban::class);
    }

    public function hasilKuis()
    {
        return $this->hasMany(HasilKuis::class);
    }

    public function isExpired()
    {
        return $this->deadline && now()->gt($this->deadline);
    }
}
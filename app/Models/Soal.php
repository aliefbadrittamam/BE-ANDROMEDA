<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'soal';
    
    protected $fillable = [
        'judul', 'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d',
        'jawaban_benar', 'gambar', 'video', 'admin_id'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function kuis()
    {
        return $this->belongsToMany(Kuis::class, 'kuis_soal')->withPivot('urutan')->withTimestamps();
    }

    public function userJawaban()
    {
        return $this->hasMany(UserJawaban::class);
    }
}

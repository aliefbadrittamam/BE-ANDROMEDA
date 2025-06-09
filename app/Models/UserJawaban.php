<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJawaban extends Model
{
    protected $table = 'user_jawaban';
    
    protected $fillable = [
        'user_id', 'kuis_id', 'soal_id', 'jawaban_user', 'is_correct', 'waktu_jawab'
    ];

    protected $casts = [
        'waktu_jawab' => 'datetime',
        'is_correct' => 'boolean',
    ];

    // Enable timestamps (created_at, updated_at)
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kuis()
    {
        return $this->belongsTo(Kuis::class);
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }
}
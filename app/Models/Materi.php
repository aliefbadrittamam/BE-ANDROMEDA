<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materi';
    
    protected $fillable = [
        'judul', 'konten_materi', 'gambar', 'video', 'admin_id'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $table = 'user_activity_log';
    
    protected $fillable = [
        'user_id', 'activity_type', 'activity_description', 'timestamp'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->timestamp) {
                $model->timestamp = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
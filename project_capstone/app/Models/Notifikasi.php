<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\NotificationType;

class Notifikasi extends Model
{
    // Jika tidak pakai timestamps (karena hanya created_at di migration)
    public $timestamps = false;

    protected $table = 'notifications';
    
    protected $fillable = [
        'user_id', // tambahkan ini
        'title',
        'message',
        'type',
        'link',
        'is_read',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'type' => NotificationType::class, // enum casting
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}

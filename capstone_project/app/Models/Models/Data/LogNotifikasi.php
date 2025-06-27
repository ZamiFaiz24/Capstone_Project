<?php

namespace App\Models\Models\Data;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TipeNotifikasi;


class LogNotifikasi extends Model
{
    protected $table = 'log_notifikasi'; // sesuaikan jika nama tabel bukan jamak

    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'tautan',
        'sudah_dibaca',
        'dibuat_pada',

    ];

    public $timestamps = false; // karena kita pakai kolom `dibuat_pada` manual

    protected $casts = [
        'sudah_dibaca' => 'boolean',
        'dibuat_pada' => 'datetime',
        'tipe' => TipeNotifikasi::class, // enum casting
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}

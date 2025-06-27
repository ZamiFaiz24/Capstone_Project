<?php

namespace App\Models\Models\Data;

use Illuminate\Database\Eloquent\Model;

class HargaTelur extends Model
{
    protected $table = 'harga_telur';

    protected $fillable = [
        'tanggal',
        'harga',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}

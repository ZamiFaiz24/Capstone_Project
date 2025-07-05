<?php

namespace App\Models\Models\Data;

use Illuminate\Database\Eloquent\Model;

class DataKlaster extends Model
{
    protected $table = 'data_klaster';

    protected $fillable = [
        'data_id',
        'berat_telur',
        'intensitas',
        'klaster',
        'waktu_klaster',
    ];
}

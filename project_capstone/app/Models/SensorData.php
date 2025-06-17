<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'data_sensor';

    protected $fillable = [
        'berat',
        'status',
    ];

    public function klasterisasi()
    {
    return $this->hasOne(DataKlasterisasi::class, 'data_id');
    }

}

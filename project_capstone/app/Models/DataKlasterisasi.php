<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class DataKlasterisasi extends Model
{
    use HasFactory;

    protected $table = 'data_klasterisasi';

    protected $fillable = [
        'data_id',
        'klaster_id',
        'berat',
        'klaster',
    ];

    // relasi ke model data sensor
    public function dataSensor()
    {
        return $this->belongsTo(SensorData::class, 'data_id');
    }
}

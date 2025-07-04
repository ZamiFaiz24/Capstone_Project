<?php

namespace App\Models\Models\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_data';

    protected $fillable = [
        'data_id',
        'berat',
        'intensitas',
        'status',
        'dibuat_pada',
    ];

    public $timestamps = false;
}

<?php

namespace App\Models\Models\Data;

use Illuminate\Database\Eloquent\Model;

class PerangkatStatus extends Model
{
    protected $table = 'data_status_perangkat';


    protected $fillable = ['status', 'dibuat_pada'];

    public $timestamps = false; // matikan otomatis created_at & updated_at

    // Atau jika kamu tetap ingin pakai timestamps tapi dengan nama custom
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
}

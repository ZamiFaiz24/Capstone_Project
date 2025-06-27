<?php

namespace App\Enums;

enum TipeNotifikasi: string
{
    case ADMIN = 'admin';
    case PERANGKAT = 'perangkat';
    case SISTEM = 'sistem';
    case EKSPOR = 'ekspor';
    case WADAH = 'wadah';
}

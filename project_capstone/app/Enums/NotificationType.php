<?php

namespace App\Enums;

enum NotificationType: string
{
    case ADMIN = 'admin';
    case DEVICE = 'device';
    case SYSTEM = 'system';
    case EXPORT = 'export';
    case WADAH = 'wadah';
}
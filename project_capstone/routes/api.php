<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Data\DataSensorController;
use App\Http\Controllers\Data\DataKlasterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthenticationController;


Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/register', [AuthenticationController::class, 'register']);
});

// Semua route lain hanya bisa diakses jika user sudah login
Route::middleware('auth:sanctum')->group(function () {
    // Data Sensor
    Route::get('/data_sensor', [DataSensorController::class, 'index']);
    Route::get('/data_klasterisasi', [DataKlasterController::class, 'index']);

    // Notifikasi
    Route::get('/notifikasi', [NotificationController::class, 'forAuthenticatedUser']);
    Route::post('/notifikasi', [NotificationController::class, 'store']);
    Route::patch('/notifikasi/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifikasi/mark-read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifikasi/test', [NotificationController::class, 'testCreate']);
    Route::get('/notifikasi/user/{id}', [NotificationController::class, 'getUserNotifications']);
    Route::get('/notifications/device', [NotificationController::class, 'deviceNotifications']);

    // Device
    Route::post('/device/log', [NotificationController::class, 'storeDeviceLog']);
    Route::get('/device/status', [NotificationController::class, 'getLastDeviceStatus']);
    Route::get('/device-logs', [NotificationController::class, 'getDeviceLogs']);
    Route::post('/device-status', [NotificationController::class, 'updateDeviceStatus']);

    // Logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});
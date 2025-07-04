<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotifikasiWadahController;
use App\Http\Controllers\Api\PrediksiController;
use App\Http\Controllers\Api\DeviceController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', [DeviceController::class, 'getStatus']);
Route::post('/status', [DeviceController::class, 'setStatus']);
Route::post('/sensor-data', [DeviceController::class, 'storeSensorData']);

Route::post('/trigger-wadah-penuh', [NotifikasiWadahController::class, 'trigger']);


Route::get('/data-harga', [PrediksiController::class, 'dataHarga']);
Route::get('/prediksi-harga', [PrediksiController::class, 'prediksiHarga']);


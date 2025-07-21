<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Models\Data\SensorData;
use App\Http\Controllers\Api\NotifikasiWadahController;
use App\Http\Controllers\Api\PrediksiController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\LaporanBIController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', [DeviceController::class, 'getStatus']);
Route::post('/status', [DeviceController::class, 'setStatus']);
Route::post('/sensor-data', [DeviceController::class, 'storeSensorData']);

Route::get('/sensor-data', function () {
    return response()->json([
        'data' => SensorData::orderBy('dibuat_pada', 'desc')->get(),
    ]);
});

Route::post('/manual-input', [PrediksiController::class, 'storeManualData']);

// routes/api.php
Route::post('/klasterisasi/manual', [PrediksiController::class, 'klasterisasiManual']);
Route::post('/klasterisasi/threshold', [PrediksiController::class, 'klasterisasiManualThreshold']);
Route::get('/sensor/klaster', [PrediksiController::class, 'getDataKlaster']);


Route::get('/data-harga', [PrediksiController::class, 'dataHarga']);
Route::get('/prediksi-harga', [PrediksiController::class, 'prediksiHarga']);


Route::get('/laporan/harga', [LaporanBIController::class, 'getHargaLaporan']);
Route::get('/laporan/produksi', [LaporanBIController::class, 'getProduksiLaporan']);
Route::get('/laporan/pendapatan', [LaporanBIController::class, 'getPendapatanLaporan']);

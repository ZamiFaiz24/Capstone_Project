<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Data\DataSensorController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('user/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('/data', [DataSensorController::class, 'index']);
Route::post('/data', [DataSensorController::class, 'store']);
Route::get('/perangkat', function () {
    return Inertia::render('user/Perangkat');
})->name('perangkat');

Route::get('/data_sensor', function () {
    return Inertia::render('user/DataSensor');
})->name('data_sensor');


Route::get('/klaster', function () {
    return Inertia::render('user/Klasterisasi');
})->name('klaster');

Route::get('/visualisasi', function () {
    return Inertia::render('user/Visualisasi');
})->name('visualisasi');

Route::get('/laporan', function () {
    return Inertia::render('user/Laporan');
})->name('laporan');
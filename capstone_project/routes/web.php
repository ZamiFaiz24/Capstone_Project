<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Admin\DataBeratController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PerangkatController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\NotifikasiController;
use App\Http\Controllers\Admin\VisualisasiController;
use App\Http\Controllers\User\DashboardUserController;
use App\Http\Middleware\AdminOnly;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman awal (public)
Route::get('/', fn() => Inertia::render('Welcome'))->name('home');

// Halaman Login
Route::get('/login', fn() => Inertia::render('auth/Login'))->name('login')->middleware('guest');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        $role = Auth::user()->role;
        return redirect()->route($role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ])->onlyInput('email');
})->middleware('guest');

// Halaman Register
Route::get('/register', fn() => Inertia::render('auth/Register'))->name('register')->middleware('guest');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Redirect berdasarkan role
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return redirect()->route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
});


// =============================
// 🔐 ADMIN ROUTES
// =============================
Route::middleware([
    'auth',
    'verified',
    AdminOnly::class // ← gunakan class middleware di sini
])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/auth/reset-password', function () {
    return Inertia::render('auth/ResetPassword');
})->name('auth.resetpassword');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/perangkat', [PerangkatController::class, 'logPerangkat'])->name('perangkat');
    Route::post('/perangkat/toggle', [PerangkatController::class, 'togglePerangkat'])->name('perangkat.toggle');

    Route::get('/data_sensor', [DashboardController::class, 'dataSensor'])->name('data_sensor');
    Route::get('/klaster', [DashboardController::class, 'klasterisasi'])->name('klaster');
    Route::post('/trigger-wadah-penuh', [NotifikasiController::class, 'trigger']);


    Route::get('/visualisasi', [VisualisasiController::class, 'index'])->name('admin.visualisasi');
    Route::get('/data-harga', [VisualisasiController::class, 'getDataHarga']);


    Route::get('/laporan', [LaporanController::class, 'ambilHarga'])->name('laporan');
    Route::post('/laporan/harga-telur', [LaporanController::class, 'store'])->name('harga.store');

    Route::get('/notifikasi-baru', [NotifikasiController::class, 'notifikasiBaru'])->name('notifikasi.baru');
    Route::get('/notifikasi/jumlah', [NotifikasiController::class,'jumlahBelumDibaca'])->name('notifikasi.jumlah');
    Route::patch('/notifikasi/{id}/baca', [NotifikasiController::class, 'tandaiDibaca'])->name('notifikasi.baca');
    Route::patch('/notifikasi/baca-semua', [NotifikasiController::class, 'tandaiSemuaDibaca']);
    Route::get('/notifikasi-log', [NotifikasiController::class, 'allLog'])->name('admin.notifikasi.log');

});

// =============================
// 👨‍🌾 USER (PETERNAK) ROUTES
// =============================
Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardUserController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [LaporanController::class, 'ambilHarga'])->name('laporan');
    Route::get('/laporan-user', fn() => Inertia::render('user/LaporanUser'))->name('laporan_user');
});


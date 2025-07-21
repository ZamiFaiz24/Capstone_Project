<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
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
use App\Http\Controllers\Api\PrediksiController;
use App\Http\Controllers\Api\NotifikasiWadahController;

use App\Http\Middleware\AdminOnly;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Semua route harus berada di dalam grup middleware 'web' agar bisa dibaca
| oleh Ziggy dan mendukung fitur-fitur seperti session, CSRF, dll.
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {

    // ─── Public ───────────────────────────────────────────────
    Route::get('/', fn() => Inertia::render('Welcome'))->name('home');

    // Login
    Route::get('/login', fn() => Inertia::render('auth/Login'))->middleware('guest')->name('login');
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

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    })->middleware('guest');

    // Register
    Route::get('/register', fn() => Inertia::render('auth/Register'))->middleware('guest')->name('register');

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    // Auto redirect dashboard sesuai role
    Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
        return redirect()->route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
    });

    // ─── ADMIN ROUTES ──────────────────────────────────────────
    Route::middleware(['auth', 'verified', AdminOnly::class])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
        // Profile
            Route::get('/profile', [ProfileController::class, 'editAdmin'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'updateAdmin'])->name('profile.update');
            Route::get('/reset-password', [ProfileController::class, 'editAdminPassword'])->name('password.edit');
            Route::put('/reset-password', [ProfileController::class, 'updateAdminPassword'])->name('password.update');

            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Perangkat
            Route::get('/perangkat', [PerangkatController::class, 'logPerangkat'])->name('perangkat');
            Route::post('/perangkat/toggle', [PerangkatController::class, 'togglePerangkat'])->name('perangkat.toggle');

            // Sensor dan Klasterisasi
            Route::post('/manual-input', [PrediksiController::class, 'storeManualData']);
            Route::get('/data_sensor', [DashboardController::class, 'dataSensor'])->name('data_sensor');
            Route::get('/klaster', [DashboardController::class, 'klasterisasi'])->name('klaster');
            // di routes/web.php
            Route::post('/klasterisasi/threshold', [PrediksiController::class, 'klasterisasiManualThreshold']);

            // Wadah Notifikasi
            Route::post('/trigger-wadah-penuh', [NotifikasiWadahController::class, 'trigger']);
            Route::post('/reset-wadah/{klaster}', [NotifikasiWadahController::class, 'resetWadah']);
            Route::get('/wadah-status', [NotifikasiWadahController::class, 'getStatus']);

            // Visualisasi
            Route::get('/visualisasi', [VisualisasiController::class, 'index'])->name('visualisasi');
            Route::get('/data-harga', [VisualisasiController::class, 'getDataHarga']);

            // Laporan
            Route::get('/laporan', [LaporanController::class, 'ambilHarga'])->name('laporan');
            Route::post('/laporan/harga-telur', [LaporanController::class, 'store'])->name('harga.store');

            // Notifikasi
            Route::get('/notifikasi-baru', [NotifikasiController::class, 'notifikasiBaru'])->name('notifikasi.baru');
            Route::get('/notifikasi/jumlah', [NotifikasiController::class, 'jumlahBelumDibaca'])->name('notifikasi.jumlah');
            Route::patch('/notifikasi/{id}/baca', [NotifikasiController::class, 'tandaiDibaca'])->name('notifikasi.baca');
            Route::patch('/notifikasi/baca-semua', [NotifikasiController::class, 'tandaiSemuaDibaca']);
            Route::get('/notifikasi-log', [NotifikasiController::class, 'allLog'])->name('notifikasi.log');
        });

    // ─── USER ROUTES ───────────────────────────────────────────
    Route::middleware(['auth', 'verified'])
        ->prefix('user')
        ->name('user.')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [DashboardUserController::class, 'index'])->name('dashboard');

            // Laporan
            Route::get('/laporan', [LaporanController::class, 'ambilHarga'])->name('laporan');
            Route::get('/laporan-user', fn() => Inertia::render('user/LaporanUser'))->name('laporan_user');

            // Profile
            Route::get('/profile', [ProfileController::class, 'editUser'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'updateUser'])->name('profile.update');
            Route::get('/reset-password', [ProfileController::class, 'editUserPassword'])->name('password.edit');
            Route::put('/reset-password', [ProfileController::class, 'updateUserPassword'])->name('password.update');
        });
});

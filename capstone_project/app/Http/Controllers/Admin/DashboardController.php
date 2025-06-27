<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Models\Data\PerangkatStatus;
use App\Models\Models\Data\HargaTelur;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Halaman utama dashboard
    public function index()
    {
        $hargaHariIni = Carbon::today();
        $hargaKemarin = Carbon::yesterday();
        
        $hargaHariIni = HargaTelur::orderByDesc('tanggal')->first();
        $hargaKemarin = HargaTelur::whereDate('tanggal', now()->subDay()->toDateString())->first();

        $persentaseKenaikan = null;
        if ($hargaHariIni && $hargaKemarin && $hargaKemarin->harga > 0) {
            $selisih = $hargaHariIni->harga - $hargaKemarin->harga;
            $persentaseKenaikan = round(($selisih / $hargaKemarin->harga) * 100);
        }

        $statusPerangkat = PerangkatStatus::latest()->first()?->status ?? 'OFF'; // Misal field status: 'ON'/'OFF'
        return Inertia::render('admin/Dashboard', [
            'statusPerangkat' => $statusPerangkat,
            'hargaHariIni' => $hargaHariIni,
            'persentaseKenaikan' => $persentaseKenaikan,
            // ...data lain
        ]);
    }

    // Halaman perangkat
    public function perangkat()
    {
        return Inertia::render('admin/Perangkat', [
            'title' => 'Kontrol Perangkat',
        ]);
    }

    // Halaman data sensor
    public function dataSensor()
    {
        return Inertia::render('admin/DataSensor', [
            'title' => 'Data Sensor',
        ]);
    }

    // Halaman klasterisasi
    public function klasterisasi()
    {
        return Inertia::render('admin/Klasterisasi', [
            'title' => 'Hasil Klasterisasi',
        ]);
    }

    // Halaman visualisasi
    public function visualisasi()
    {
        return Inertia::render('admin/Visualisasi', [
            'title' => 'Visualisasi Data',
        ]);
    }

    // Halaman laporan
    public function laporan()
    {
        return Inertia::render('admin/Laporan', [
            'title' => 'Laporan',
        ]);
    }

}

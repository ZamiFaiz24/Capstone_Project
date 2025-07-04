<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Models\Data\PerangkatStatus;
use App\Models\Models\Data\HargaTelur;
use App\Models\Models\Data\LogNotifikasi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Halaman utama dashboard
    public function index()
    {
        // Ambil harga hari ini dan kemarin
        $hargaHariIni = HargaTelur::orderByDesc('tanggal')->first();
        $hargaKemarin = HargaTelur::whereDate('tanggal', now()->subDay()->toDateString())->first();

        // Hitung persentase kenaikan
        $persentaseKenaikan = null;
        if ($hargaHariIni && $hargaKemarin && $hargaKemarin->harga > 0) {
            $selisih = $hargaHariIni->harga - $hargaKemarin->harga;
            $persentaseKenaikan = round(($selisih / $hargaKemarin->harga) * 100);
        }

        // Ambil status perangkat terakhir
        $statusPerangkat = PerangkatStatus::latest()->first()?->status ?? 'OFF';

        $logPerangkat = LogNotifikasi::where('tipe', 'perangkat')
            ->latest('dibuat_pada')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $status = '-';

                if (str_contains(strtolower($item->pesan), 'dinyalakan')) {
                    $status = 'ON';
                } elseif (str_contains(strtolower($item->pesan), 'dimatikan')) {
                    $status = 'OFF';
                }

                return [
                    'id' => $item->id,
                    'waktu' => $item->dibuat_pada->format('d M Y, H:i'),
                    'perangkat' => $item->judul,
                    'status' => $status,
                    'catatan' => $item->pesan,
                ];
            });


        return Inertia::render('admin/Dashboard', [
            'statusPerangkat' => $statusPerangkat,
            'hargaHariIni' => $hargaHariIni,
            'persentaseKenaikan' => $persentaseKenaikan,
            'logPerangkatData' => $logPerangkat, // Tanpa spasi di akhir!
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

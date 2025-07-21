<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanBIController extends Controller
{
    public function getHargaLaporan(Request $request)
    {
        $periode = $request->query('periode', 'month'); // default: month
        $query = DB::table('harga_telur');

        // Filter tanggal
        if ($periode === 'today') {
            $query->whereDate('tanggal', now()->toDateString());
        } elseif ($periode === 'yesterday') {
            $query->whereDate('tanggal', now()->subDay()->toDateString());
        } elseif ($periode === 'week') {
            $query->whereBetween('tanggal', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($periode === 'month') {
            $query->whereBetween('tanggal', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        }

        $data = $query->orderBy('tanggal', 'desc')->get();

        if ($data->isEmpty()) {
            return response()->json([
                'harga_sekarang' => null,
                'tanggal_sekarang' => null,
                'harga_tertinggi' => null,
                'tanggal_tertinggi' => null,
                'harga_terendah' => null,
                'tanggal_terendah' => null,
            ]);
        }

        $hargaSekarang = $data->first();
        $tertinggi = $data->sortByDesc('harga')->first();
        $terendah = $data->sortBy('harga')->first();

        return response()->json([
            'harga_sekarang' => $hargaSekarang->harga,
            'tanggal_sekarang' => $hargaSekarang->tanggal,
            'harga_tertinggi' => $tertinggi->harga,
            'tanggal_tertinggi' => $tertinggi->tanggal,
            'harga_terendah' => $terendah->harga,
            'tanggal_terendah' => $terendah->tanggal,
        ]);
    }


    public function getProduksiLaporan(Request $request)
    {
        $periode = $request->query('periode', 'month'); // default: bulan

        $query = DB::table('sensor_data')
            ->selectRaw('DATE(dibuat_pada) as tanggal, COUNT(*) as jumlah')
            ->where('intensitas', '>=', 15); // hanya telur layak

        // Filter waktu
        if ($periode === 'today') {
            $query->whereDate('dibuat_pada', now()->toDateString());
        } elseif ($periode === 'yesterday') {
            $query->whereDate('dibuat_pada', now()->subDay()->toDateString());
        } elseif ($periode === 'week') {
            $query->whereBetween('dibuat_pada', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($periode === 'month') {
            $query->whereBetween('dibuat_pada', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        }

        $data = $query->groupByRaw('DATE(dibuat_pada)')->get();

        $total = array_sum($data->pluck('jumlah')->toArray());
        $rataProduksi = round($data->avg('jumlah'));
        $tertinggi = $data->sortByDesc('jumlah')->first();
        $terendah = $data->sortBy('jumlah')->first();

        return response()->json([
            'rata' => $rataProduksi,
            'tertinggi' => $tertinggi->jumlah ?? 0,
            'tanggal_tertinggi' => $tertinggi->tanggal ?? '-',
            'terendah' => $terendah->jumlah ?? 0,
            'tanggal_terendah' => $terendah->tanggal ?? '-',
            'total' => $total,
        ]);
    }

    public function getPendapatanLaporan(Request $request)
    {
        $periode = $request->query('periode', 'month');

        // Set rentang tanggal
        $start = now();
        $end = now();

        if ($periode === 'today') {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        } elseif ($periode === 'yesterday') {
            $start = now()->subDay()->startOfDay();
            $end = now()->subDay()->endOfDay();
        } elseif ($periode === 'week') {
            $start = now()->subDays(6)->startOfDay();
        } elseif ($periode === 'month') {
            $start = now()->subDays(29)->startOfDay();
        }

        // Ambil jumlah produksi per tanggal
        $produksi = DB::table('sensor_data')
            ->selectRaw('DATE(dibuat_pada) as tanggal, COUNT(*) as jumlah_produksi')
            ->where('intensitas', '>=', 15)
            ->whereBetween('dibuat_pada', [$start, $end])
            ->groupByRaw('DATE(dibuat_pada)')
            ->pluck('jumlah_produksi', 'tanggal');

        // Ambil harga per tanggal
        $harga = DB::table('harga_telur')
            ->selectRaw('DATE(tanggal) as tanggal, harga')
            ->whereBetween('tanggal', [$start, $end])
            ->pluck('harga', 'tanggal');

        // Hitung pendapatan: produksi * harga per tanggal
        $pendapatan = [];
        foreach ($produksi as $tanggal => $jumlah) {
            $hargaPerTanggal = $harga[$tanggal] ?? null;
            if ($hargaPerTanggal !== null) {
                $pendapatan[$tanggal] = $jumlah * $hargaPerTanggal;
            }
        }

        if (empty($pendapatan)) {
            return response()->json([
                'tertinggi' => 0,
                'tanggal_tertinggi' => '-',
                'terendah' => 0,
                'tanggal_terendah' => '-',
                'rata' => 0,
                'total' => 0,
            ]);
        }

        $total = array_sum($pendapatan);

        // Ambil pendapatan tertinggi dan terendah
        $tanggalTertinggi = array_keys($pendapatan, max($pendapatan))[0];
        $tanggalTerendah = array_keys($pendapatan, min($pendapatan))[0];

        return response()->json([
            'tertinggi' => $pendapatan[$tanggalTertinggi],
            'tanggal_tertinggi' => $tanggalTertinggi,
            'terendah' => $pendapatan[$tanggalTerendah],
            'tanggal_terendah' => $tanggalTerendah,
            'rata' => round(array_sum($pendapatan) / count($pendapatan)),
            'total' => $total,
        ]);
    }
}

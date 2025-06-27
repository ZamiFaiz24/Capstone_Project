<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Models\Data\HargaTelur;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function ambilHarga()
    {
        $data = HargaTelur::orderBy('tanggal')->get();
        return inertia('admin/Laporan', [
            'hargaData' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:harga_telur,tanggal',
            'harga' => 'required|numeric',
        ]);

        // Ambil harga terakhir untuk menentukan keterangan
        $hargaTerakhir = HargaTelur::orderByDesc('tanggal')->first();
        $keterangan = 'Stabil';

        if ($hargaTerakhir) {
            if ($request->harga > $hargaTerakhir->harga) {
                $keterangan = 'Naik';
            } elseif ($request->harga < $hargaTerakhir->harga) {
                $keterangan = 'Turun';
            }
        }

        HargaTelur::create([
            'tanggal' => $request->tanggal,
            'harga' => $request->harga,
            'keterangan' => $keterangan,
        ]);

        return redirect()->back()->with('success', 'Harga berhasil ditambahkan.');
    }
}

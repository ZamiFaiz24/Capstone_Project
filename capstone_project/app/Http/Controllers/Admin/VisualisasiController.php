<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Models\Data\HargaTelur;

class VisualisasiController extends Controller
{
    // Untuk halaman tampilan
    public function index()
    {
        return Inertia::render('admin/Visualisasi'); // tanpa data atau cukup data awal saja
    }

    // Untuk fetch data ke Chart.js
    public function getDataHarga()
    {
        return HargaTelur::orderBy('tanggal', 'asc')->get(['tanggal', 'harga']);
    }
}

<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\DataKlasterisasi;
use Illuminate\Http\Request;

class DataKlasterController extends Controller
{
    // Get semua data klasterisasi
    public function index()
    {
        $data = DataKlasterisasi::with('dataSensor')->get();
        return response()->json($data);
    }

    // Simpan data klasterisasi baru
    public function store(Request $request)
    {
        $request->validate([
            'data_id' => 'required|exists:data_sensor,id',
            'klaster_id' => 'required|string',
            'berat' => 'required|numeric',
            'klaster' => 'required|in:besar,kecil',
        ]);

        $data = DataKlasterisasi::create($request->all());

        return response()->json($data, 201);
    }

    // Tampilkan detail data klasterisasi berdasarkan ID
    public function show($id)
    {
        $data = DataKlasterisasi::with('dataSensor')->find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($data);
    }

    // Update data
    public function update(Request $request, $id)
    {
        $data = DataKlasterisasi::find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'klaster_id' => 'string|nullable',
            'berat' => 'numeric|nullable',
            'klaster' => 'in:besar,kecil|nullable',
        ]);

        $data->update($request->all());

        return response()->json($data);
    }

    // Hapus data
    public function destroy($id)
    {
        $data = DataKlasterisasi::find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}

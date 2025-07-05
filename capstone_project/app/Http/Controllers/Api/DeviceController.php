<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Models\Data\SensorData;
use App\Models\Models\Data\DataKlaster;


class DeviceController extends Controller
{
    public function getStatus()
    {
        $status = Cache::get('device_status', 'OFF');
        return response()->json(['status' => $status]);
    }

    public function setStatus(Request $request)
    {
        $status = $request->input('status', 'OFF');
        Cache::put('device_status', $status, now()->addMinutes(30));
        return response()->json(['message' => 'Status updated', 'status' => $status]);
    }

    public function storeSensorData(Request $request)
    {
        $validated = $request->validate([
            'sensor_id' => 'nullable|string',
            'berat' => 'required|numeric',
            'intensitas' => 'required|numeric', // ✅ tambahkan validasi ini
        ]);

        $sensor = SensorData::create($validated); // Simpan ke DB

        Log::info('📡 Data dari ESP diterima dan disimpan: ID = ' . $sensor->id);

        // Kirim ke Flask untuk klasterisasi
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('http://127.0.0.1:5000/predict', [
            'berat' => $sensor->berat,
            'intensitas' => $sensor->intensitas, // ✅ kirim juga intensitas
        ]);

        if ($response->successful()) {
            $klaster = $response->json()['klaster'] ?? null;

            Log::info('✅ Hasil klaster dari Flask: ' . $klaster);

            if ($klaster !== null) {
                DataKlaster::create([
                    'data_id' => $sensor->id,
                    'berat_telur' => $sensor->berat,
                    'intensitas' => $sensor->intensitas, // ✅ simpan juga intensitas
                    'klaster' => $klaster,
                    'waktu_klaster' => now(),
                ]);
                Log::info('💾 Hasil klaster disimpan ke DB');
            }
        } else {
            Log::error('❌ Gagal mendapatkan klaster dari Flask');
        }

        return response()->json([
            'message' => 'Data dan klaster berhasil diproses',
            'klaster' => $klaster ?? null,
        ]);
    }
}

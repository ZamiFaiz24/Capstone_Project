<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Models\Data\SensorData;
use App\Models\Models\Data\DataKlaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'intensitas' => 'required|numeric',
        ]);

        // 1. Simpan data sensor
        $sensor = SensorData::create([
            'sensor_id' => $validated['sensor_id'] ?? null,
            'berat' => $validated['berat'],
            'intensitas' => $validated['intensitas'],
            'dibuat_pada' => now(),
        ]);

        Log::info('📡 Data dari ESP diterima: ID = ' . $sensor->id);

        // 2. Gunakan threshold (tanpa Flask)
        $berat = $sensor->berat;
        $lux = $sensor->intensitas;

        $klaster = null;
        $label_ukuran = null;
        $label_kualitas = null;
        $label_tampilan = null;

        if ($lux < 30) {
            // Telur busuk
            $klaster = 2;
            $label_ukuran = 'Tidak Tersaring';
            $label_kualitas = 'Mutu Rendah';
            $label_tampilan = 'Mutu Rendah';
        } else {
            if ($berat <= 60) {
                $klaster = 1;
                $label_ukuran = 'Kecil';
                $label_kualitas = 'Mutu Tinggi';
                $label_tampilan = 'Kecil Mutu Tinggi';
            } else {
                $klaster = 0;
                $label_ukuran = 'Besar';
                $label_kualitas = 'Mutu Tinggi';
                $label_tampilan = 'Besar Mutu Tinggi';
            }
        }

        $userId = Auth::id();
        $batasTelur = 5;

        $jumlahTelur = DB::table('data_klaster')
            ->where('label_tampilan', $label_tampilan)
            ->count();

        if ($jumlahTelur >= $batasTelur) {
            DB::table('log_notifikasi')->insert([
                'user_id' => $userId,
                'judul' => 'Penolakan Otomatis',
                'pesan' => "Telur dari IoT gagal masuk ke klaster $label_tampilan karena wadah penuh.",
                'tipe' => 'sistem',
                'tautan' => '/admin/klaster',
                'sudah_dibaca' => 0,
                'dibuat_pada' => now(),
            ]);

            return response()->json(['message' => 'Wadah penuh, data tidak disimpan'], 400);
        }

        // Simpan data klaster
        DataKlaster::create([
            'data_id' => $sensor->id,
            'berat_telur' => $berat,
            'intensitas' => $lux,
            'klaster' => $klaster,
            'label_ukuran' => $label_ukuran,
            'label_kualitas' => $label_kualitas,
            'label_tampilan' => $label_tampilan,
            'waktu_klaster' => now(),
        ]);

        // Cek penuh setelah insert
        if (($jumlahTelur + 1) >= $batasTelur) {
            $sudahAda = DB::table('log_notifikasi')
                ->where('tipe', 'wadah')
                ->where('pesan', 'like', "%$label_tampilan%")
                ->where('sudah_dibaca', 0)
                ->exists();

            if (!$sudahAda) {
                DB::table('log_notifikasi')->insert([
                    'user_id' => $userId,
                    'judul' => 'Wadah Penuh',
                    'pesan' => "Wadah telur $label_tampilan penuh (isi " . ($jumlahTelur + 1) . " telur)",
                    'tipe' => 'wadah',
                    'tautan' => '/admin/klaster',
                    'sudah_dibaca' => 0,
                    'dibuat_pada' => now(),
                ]);
            }
        }

        Log::info("✅ Data klaster $label_tampilan berhasil disimpan");

        return response()->noContent(); // tetap 204 ke ESP
    }
}

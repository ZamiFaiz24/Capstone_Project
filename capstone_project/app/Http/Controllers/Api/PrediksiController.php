<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Models\Data\HargaTelur;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PrediksiController extends Controller
{
    public function prediksiHarga(Request $request)
    {
        $days = $request->query('days', 7); // default 7 hari
        $flaskUrl = "http://127.0.0.1:5001/api/prediksi-harga?days=$days";

        try {
            $response = Http::timeout(5)->get($flaskUrl);

            if ($response->successful()) {
                $data = collect($response->json())->map(function ($item) {
                    return [
                        'tanggal' => $item['ds'],              // ubah 'ds' ke 'tanggal'
                        'harga' => round($item['yhat']),       // bulatkan harga prediksi
                    ];
                });

                return response()->json([
                    'status' => 'success',
                    'data' => $data
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal ambil prediksi dari model.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal koneksi ke server Flask.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dataHarga()
    {
        $data = HargaTelur::select('tanggal', 'harga')->orderBy('tanggal')->get();

        return response()->json($data);
    }

    public function getDataKlaster()
    {
        $data = DB::table('data_klaster')
            ->join('sensor_data', 'sensor_data.id', '=', 'data_klaster.data_id')
            ->select(
                'sensor_data.id as id',
                'sensor_data.berat as berat',
                'sensor_data.intensitas as intensitas',
                'data_klaster.klaster',
                'data_klaster.label_ukuran',
                'data_klaster.label_kualitas',
                'data_klaster.label_tampilan',
                'data_klaster.waktu_klaster as waktu_masuk',
            )
            ->orderByDesc('data_klaster.waktu_klaster')
            ->get();

        return response()->json($data);
    }

    public function klasterisasiManual(Request $request)
    {
        $sensors = DB::table('sensor_data')
            ->leftJoin('data_klaster', 'sensor_data.id', '=', 'data_klaster.data_id')
            ->whereNull('data_klaster.data_id')
            ->select('sensor_data.id', 'sensor_data.berat', 'sensor_data.intensitas', 'sensor_data.dibuat_pada')
            ->get();

        if ($sensors->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data baru untuk diklasterisasi.'], 200);
        }

        foreach ($sensors as $sensor) {
            $response = Http::post('http://127.0.0.1:5001/predict', [
                'berat' => $sensor->berat,
                'intensitas' => $sensor->intensitas,
            ]);

            $result = $response->json();

            if (!isset($result['klaster'])) {
                Log::error("Respons Flask tidak sesuai untuk ID: " . $sensor->id);
                continue;
            }

            DB::table('data_klaster')->insert([
                'data_id' => $sensor->id,
                'berat_telur' => $sensor->berat,
                'intensitas' => $sensor->intensitas,
                'klaster' => $result['klaster'],
                'label_ukuran' => $result['label_ukuran'] ?? null,
                'label_kualitas' => $result['label_kualitas'] ?? null,
                'label_tampilan' => $result['label_tampilan'] ?? null,
                'waktu_klaster' => $sensor->dibuat_pada,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Berhasil klasterisasi semua data baru.'], 200);
    }

    public function storeManualData(Request $request)
    {
        $validated = $request->validate([
            'berat' => 'required|numeric',
            'intensitas' => 'required|numeric',
        ]);

        // Simpan ke sensor_data
        $sensorId = DB::table('sensor_data')->insertGetId([
            'berat' => $validated['berat'],
            'intensitas' => $validated['intensitas'],
            'dibuat_pada' => now(),
        ]);

        $sensor = DB::table('sensor_data')->where('id', $sensorId)->first();

        // Kirim ke Flask
        $response = Http::post('http://127.0.0.1:5000/predict', [
            'berat' => $sensor->berat,
            'intensitas' => $sensor->intensitas,
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $labelTampilan = $result['label_tampilan'] ?? null;
            $userId = Auth::id();
            $batasTelur = 5;

            if ($labelTampilan) {
                // Hitung jumlah telur aktif (yang belum di-reset)
                $jumlahTelur = DB::table('data_klaster')
                    ->where('label_tampilan', $labelTampilan)
                    ->where('sudah_di_reset', 0)
                    ->count();

                // ❌ Wadah penuh
                if ($jumlahTelur >= $batasTelur) {
                    DB::table('log_notifikasi')->insert([
                        'user_id' => $userId,
                        'judul' => 'Penolakan Otomatis',
                        'pesan' => "Telur gagal masuk ke klaster $labelTampilan karena wadah penuh.",
                        'tipe' => 'sistem',
                        'tautan' => '/admin/klaster',
                        'sudah_dibaca' => 0,
                        'dibuat_pada' => now(),
                    ]);

                    return response()->json(['message' => 'Wadah penuh, data tidak disimpan'], 400);
                }
            }

            // ✅ Simpan data klaster baru (aktif)
            DB::table('data_klaster')->insert([
                'data_id' => $sensor->id,
                'berat_telur' => $sensor->berat,
                'intensitas' => $sensor->intensitas,
                'klaster' => $result['klaster'],
                'label_ukuran' => $result['label_ukuran'] ?? null,
                'label_kualitas' => $result['label_kualitas'] ?? null,
                'label_tampilan' => $labelTampilan,
                'sudah_di_reset' => 0, // baru masuk, belum direset
                'waktu_klaster' => $sensor->dibuat_pada,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 🔔 Kirim notifikasi jika sudah penuh setelah insert
            $jumlahFinal = $jumlahTelur + 1;
            if ($jumlahFinal >= $batasTelur) {
                $sudahAda = DB::table('log_notifikasi')
                    ->where('tipe', 'wadah')
                    ->where('pesan', 'like', "%$labelTampilan%")
                    ->where('sudah_dibaca', 0)
                    ->exists();

                if (!$sudahAda) {
                    DB::table('log_notifikasi')->insert([
                        'user_id' => $userId,
                        'judul' => 'Wadah Penuh',
                        'pesan' => "Wadah telur $labelTampilan penuh (isi $jumlahFinal telur)",
                        'tipe' => 'wadah',
                        'tautan' => '/admin/klaster/',
                        'sudah_dibaca' => 0,
                        'dibuat_pada' => now(),
                    ]);
                }
            }
        }

        return response()->noContent();
    }

    public function klasterisasiManualThreshold()
    {
        try {
            // 1. Ambil data sensor yang belum diklaster
            $dataSensor = DB::table('sensor_data')
                ->whereNotIn('id', function ($query) {
                    $query->select('data_id')->from('data_klaster');
                })
                ->get();

            if ($dataSensor->isEmpty()) {
                return response()->json(['message' => 'Tidak ada data baru untuk diklaster.']);
            }

            $userId = Auth::id();
            $batasTelur = 5;

            foreach ($dataSensor as $sensor) {
                $berat = $sensor->berat;
                $lux = $sensor->intensitas;

                // 2. Threshold Klasterisasi
                $klaster = null;
                $label_ukuran = '';
                $label_kualitas = '';
                $label_tampilan = '';

                if ($lux < 30) {
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

                // 3. Cek apakah wadah sudah penuh
                $jumlahTelur = DB::table('data_klaster')
                    ->where('label_tampilan', $label_tampilan)
                    ->count();

                if ($jumlahTelur >= $batasTelur) {
                    // Wadah penuh - kirim notifikasi
                    DB::table('log_notifikasi')->insert([
                        'user_id' => $userId,
                        'judul' => 'Penolakan Otomatis',
                        'pesan' => "Telur dari sensor ID {$sensor->id} gagal diklaster ke $label_tampilan karena wadah penuh.",
                        'tipe' => 'sistem',
                        'tautan' => '/admin/klaster',
                        'sudah_dibaca' => 0,
                        'dibuat_pada' => now(),
                    ]);
                    continue; // Lewati simpan data
                }

                // 4. Simpan ke tabel data_klaster
                DB::table('data_klaster')->insert([
                    'data_id' => $sensor->id,
                    'berat_telur' => $berat,
                    'intensitas' => $lux,
                    'klaster' => $klaster,
                    'label_ukuran' => $label_ukuran,
                    'label_kualitas' => $label_kualitas,
                    'label_tampilan' => $label_tampilan,
                    'waktu_klaster' => now(),
                ]);

                // 5. Kirim notifikasi jika wadah baru saja penuh
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
            }

            return response()->json(['message' => 'Klasterisasi manual berdasarkan threshold selesai.']);
        } catch (\Exception $e) {
            // Tangkap error dan tampilkan
            return response()->json([
                'message' => 'Terjadi kesalahan saat klasterisasi manual',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


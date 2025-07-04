<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Models\Data\HargaTelur;

class PrediksiController extends Controller
{
    public function prediksiHarga(Request $request)
    {
        $days = $request->query('days', 7); // default 7 hari
        $flaskUrl = "http://127.0.0.1:5000/api/prediksi-harga?days=$days";

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
}

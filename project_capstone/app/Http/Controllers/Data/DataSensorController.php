<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SensorData;

class DataSensorController extends Controller
{
   // Simpan data sensor
    public function store(Request $request)
    {
        $data = $request->validate([
            'data_id' => 'required|string',
            'weight' => 'required|numeric',
            'status' => 'required|in:valid,error,out_of_range',
        ]);

        $sensor = SensorData::create($data);

        return response()->json($sensor, 201);
    }

    // Ambil semua data telur
    public function index()
    {
        return response()->json(SensorData::all());
    }
}

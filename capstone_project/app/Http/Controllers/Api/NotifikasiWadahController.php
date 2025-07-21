<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Models\Data\LogNotifikasi;
use App\Events\WadahPenuhNotification;
use App\Enums\TipeNotifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class NotifikasiWadahController extends Controller
{
    // Controller: NotifikasiWadahController.php

    public function resetWadah($klaster)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $affected = DB::table('data_klaster')
            ->where('label_tampilan', $klaster)
            ->where('sudah_di_reset', 0)
            ->update(['sudah_di_reset' => 1]);

        if ($affected === 0) {
            return response()->json(['message' => 'Tidak ada data yang direset atau sudah direset sebelumnya'], 200);
        }

        DB::table('log_notifikasi')->insert([
            'user_id' => $userId,
            'judul' => 'Reset Wadah',
            'pesan' => "Wadah klaster {$klaster} telah direset.",
            'tipe' => 'wadah',
            'tautan' => '/admin/klaster',
            'sudah_dibaca' => 0,
            'dibuat_pada' => now(),
        ]);

        return response()->json(['message' => 'Wadah berhasil direset']);
    }


    public function getStatus()
    {
        $data = DB::table('data_klaster')
            ->where('sudah_di_reset', 0)
            ->select(
                'label_tampilan as nama',
                DB::raw('COUNT(*) as jumlahTelur'),
                DB::raw('SUM(berat_telur) as totalBerat')
            )
            ->groupBy('label_tampilan')
            ->get();

        return response()->json($data);
    }
}

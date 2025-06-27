<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Models\Data\LogNotifikasi;
use App\Enums\TipeNotifikasi;
use App\Events\WadahPenuhNotification;

class NotifikasiController extends Controller
{
    public function index()
    {
        return LogNotifikasi::latest()->take(10)->get(); // bisa difilter per type nanti
    }

    // Simulasi input notifikasi tipe wadah
    public function simulasiWadahPenuh()
    {
        $log = LogNotifikasi::create([
            'type' => 'wadah',
            'pesan' => 'Wadah telur telah penuh. Harap segera diganti.',
        ]);

        // Broadcast event (jika pakai Echo)
        event(new \App\Events\WadahPenuhNotification($log));

        return response()->json(['message' => 'Simulasi berhasil']);
    }

    public function notifikasiBaru(Request $request)
    {
        $userId = $request->user()->id;

        $notifikasi = LogNotifikasi::where('user_id', $userId)
            ->orderByDesc('dibuat_pada')
            ->take(10)
            ->get();

        $jumlah = $notifikasi->where('sudah_dibaca', false)->count();

        return response()->json([
            'data' => $notifikasi,
            'jumlah' => $jumlah,
        ]);
    }

    public function trigger(Request $request)
    {
        $request->validate([
            'klaster' => 'required|string',
            'id_wadah' => 'required|integer',
            'total_berat' => 'required|numeric',
            'kapasitas_max' => 'required|numeric',
        ]);

        $klaster = $request->klaster;
        $idWadah = $request->id_wadah;
        $totalBerat = $request->total_berat;
        $kapasitasMax = $request->kapasitas_max;

        if ($totalBerat >= $kapasitasMax) {
            $pesan = "Wadah klaster {$klaster} dengan ID {$idWadah} telah penuh (" . number_format($totalBerat, 2) . " kg). Segera ganti.";

            $userId = $request->user()->id; // gunakan user yang login

            $log = LogNotifikasi::create([
                'user_id' => $userId,
                'judul' => 'Wadah Telur Penuh',
                'pesan' => "Wadah {$klaster} (ID: {$idWadah}) penuh!",
                'tipe' => TipeNotifikasi::WADAH,
                'tautan' => '/admin/klaster/',
                'sudah_dibaca' => 0,
                'dibuat_pada' => now(),
            ]);

            broadcast(new WadahPenuhNotification($log));

            return response()->json(['message' => 'Notifikasi wadah penuh dikirim.']);
        }

        return response()->json(['message' => 'Wadah belum penuh, tidak ada notifikasi.']);
    }

    public function jumlahBelumDibaca(Request $request)
    {
        $jumlah = LogNotifikasi::where('user_id', $request->user()->id)
            ->where('sudah_dibaca', 0)
            ->count();

        return response()->json(['jumlah' => $jumlah]);
    }

    public function tandaiDibaca($id, Request $request)
    {
        $notif = LogNotifikasi::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($notif) {
            $notif->sudah_dibaca = 1;
            $notif->save();
            // Debug: return data notifikasi setelah update
            return response()->json([
                'message' => 'Notifikasi ditandai sebagai dibaca.',
                'notif' => $notif
            ]);
        }

        return response()->json(['message' => 'Notifikasi tidak ditemukan.'], 404);
    }

    public function tandaiSemuaDibaca(Request $request)
    {
        $userId = $request->user()->id;
        $updated = LogNotifikasi::where('user_id', $userId)
            ->where('sudah_dibaca', 0)
            ->update(['sudah_dibaca' => 1]);

        // Debug: ambil semua notifikasi user setelah update
        $notifikasi = LogNotifikasi::where('user_id', $userId)->get();

        return response()->json([
            'message' => 'Semua notifikasi ditandai sebagai dibaca.',
            'updated_count' => $updated,
            'notifikasi' => $notifikasi
        ]);
    }

    public function allLog(Request $request)
    {
    $userId = $request->user()->id;
    $logs = LogNotifikasi::where('user_id', $userId)
        ->orderBy('dibuat_pada', 'desc')
        ->get();

    return response()->json([
        'data' => $logs
    ]);
    }
}

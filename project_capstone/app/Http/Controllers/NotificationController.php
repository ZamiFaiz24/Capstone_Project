<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use App\Enums\NotificationType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    // Ambil semua notifikasi (bisa tambahkan filter user jika perlu)
    public function index(Request $request)
    {
    }

    public function forAuthenticatedUser(Request $request)
    {
        Log::info('User yang terautentikasi:', ['user' => $request->user()]);
        return response()->json(['data' => 'Berhasil login']);
    }


    // Simpan notifikasi baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'type'    => ['required', 'string'], // Validasi enum bisa lebih ketat jika perlu
            'message' => 'nullable|string',
            'link'    => 'nullable|string',
        ]);

        $notif = Notifikasi::create([
            'user_id' => $request->user()->id, // atau $request->input('user_id')
            'title'   => $validated['title'],
            'type'    => $validated['type'],
            'message' => $validated['message'] ?? null,
            'link'    => $validated['link'] ?? null,
            'is_read' => false,
            'created_at' => now(),
        ]);

        return response()->json($notif, 201);
    }

    // Tandai notifikasi sudah dibaca
    public function markAsRead($id)
    {
        Notifikasi::where('is_read', false)->update(['is_read' => true]);
        return response()->json(['message' => 'Semua notifikasi ditandai sebagai dibaca']);
    }


    // Buat notifikasi uji coba
    public function testCreate(Request $request)
    {
        $notif = Notifikasi::create([
            'user_id' => $request->user_id ?? 1, // ganti ID sesuai user
            'title' => 'Uji Notifikasi',
            'message' => 'Ini adalah notifikasi percobaan.',
            'type' => 'system',
            'link' => '/dashboard',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $notif,
        ]);
    }

    // Ambil semua notifikasi untuk user tertentu
    public function getUserNotifications($id)
    {
        Log::info("Memanggil getUserNotifications untuk user $id");

        // Ambil notifikasi yang bertipe 'device'
        $notifications = Notifikasi::where('user_id', $id)
            ->where('type', 'device')
            ->orderBy('created_at', 'desc')
            ->get();

        // Log jumlah data yang diambil
        Log::info("Jumlah notifikasi 'device' yang ditemukan: " . $notifications->count());

        // Jika data ada, log 1 atau 2 data pertama saja agar tidak terlalu banyak
        if ($notifications->count() > 0) {
            Log::info("Contoh data notifikasi:", $notifications->take(2)->toArray());
        } else {
            Log::info("Tidak ditemukan notifikasi bertipe 'device' untuk user $id.");
        }

        return response()->json([
            'data' => $notifications,
        ]);
    }

    public function getDeviceLogs()
    {
        $logs = Notifikasi::where('type', NotificationType::DEVICE)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }

    public function deviceNotifications()
    {
        $notifications = Notifikasi::where('type', NotificationType::DEVICE)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    public function storeDeviceLog(Request $request)
    {
        $request->validate([
            'perangkat' => 'required|string',
            'status' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $notif = Notifikasi::create([
            'user_id' => $request->user_id ?? 1,
            'title' => 'Status perangkat diubah',
            'message' => $request->catatan ?? 'Perubahan status',
            'type' => NotificationType::DEVICE,
            'link' => '/perangkat', // bisa disesuaikan
            'is_read' => false,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Log perangkat disimpan.', 'data' => $notif], 201);
    }

    public function updateDeviceStatus(Request $request)
    {
        $request->validate([
            'perangkat' => 'required|string',
            'status' => 'required|string', // bisa dibatasi dengan in:Aktif,Nonaktif kalau mau
            'catatan' => 'nullable|string',
        ]);

        // Simpan log ke tabel notifikasi
        $log = Notifikasi::create([
            'user_id' => 1, // kamu bisa sesuaikan ini kalau ada user login
            'title' => $request->perangkat,
            'message' => $request->catatan ?? 'Status diubah menjadi ' . $request->status,
            'type' => NotificationType::SYSTEM, // ENUM kamu
            'link' => '/dashboard',
            'is_read' => false,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Status perangkat diperbarui.', 'data' => $log], 201);
    }

    public function getLastDeviceStatus()
    {
        $lastLog = Notifikasi::where('type', NotificationType::DEVICE)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastLog) {
            return response()->json(['status' => 'unknown']); // atau default ke off
        }

        $status = str_contains(strtolower($lastLog->message), 'aktif') ? 'on' : 'off';

        return response()->json(['status' => $status]);
    }


    public function markAllAsRead()
{
    Notifikasi::where('is_read', false)->update(['is_read' => true]);

    return response()->json(['message' => 'Semua notifikasi ditandai sebagai dibaca.']);
}
}
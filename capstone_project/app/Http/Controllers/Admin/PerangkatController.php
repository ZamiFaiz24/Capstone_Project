<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Models\Data\LogNotifikasi;
use App\Models\Models\Data\PerangkatStatus;
use Illuminate\Support\Facades\Auth;
use App\Events\LogPerangkatUpdated;

class PerangkatController extends Controller
{
    public function logPerangkat()
    {
        $logs = LogNotifikasi::where('tipe', 'perangkat')
            ->orderByDesc('dibuat_pada')
            ->get();

        $status = PerangkatStatus::latest()->first()?->status === 'on';

        return Inertia::render('admin/Perangkat', [
            'logs' => $logs,
            'status' => $status, // kirim boolean: true (on), false (off)
        ]);
    }

    public function togglePerangkat(Request $request)
    {
        // Validasi input status
        $request->validate([
            'status' => 'required|in:on,off',
        ]);

        // Cek apakah status 'on' atau 'off'
        $statusBaru = $request->status === 'on';

        // Simpan status ke tabel perangkat_status
        PerangkatStatus::create([
            'status' => $statusBaru ? 'on' : 'off',
        ]);

        // Simpan ke tabel log_notifikasi
        $log = LogNotifikasi::create([
            'judul'         => 'Kontrol Perangkat',
            'pesan'         => $statusBaru ? 'Perangkat berhasil dinyalakan' : 'Perangkat berhasil dimatikan',
            'tipe'          => 'perangkat',
            'tautan'        => '/admin/perangkat',
            'sudah_dibaca'  => false,
            'dibuat_pada'   => now(),
            'user_id'       => Auth::id(),
        ]);

        // Kirim broadcast
        broadcast(new LogPerangkatUpdated($log))->toOthers();

        // Redirect balik ke halaman perangkat
        return redirect()->back()->with('status', $statusBaru);
    }
}

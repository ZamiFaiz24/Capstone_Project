<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            // Hapus kolom yang tidak dibutuhkan
            $table->dropColumn(['status']);

            // Pastikan tipe data sesuai
            $table->float('berat')->change();
            $table->float('intensitas')->change();

            // Ubah kolom dibuat_pada menjadi timestamp dengan default sekarang
            $table->timestamp('dibuat_pada')->default(DB::raw('CURRENT_TIMESTAMP'))->change();
        });
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            // Tambahkan kembali kolom yang dihapus jika ingin rollback
            $table->string('status')->nullable();

            // Kembalikan ke tipe default (optional)
            $table->float('berat')->change();
            $table->float('intensitas')->change();
            $table->timestamp('dibuat_pada')->nullable()->change();
        });
    }
};

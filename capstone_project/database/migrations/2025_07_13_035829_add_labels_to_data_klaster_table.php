<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_klaster', function (Blueprint $table) {
            $table->string('label_prediksi')->nullable()->after('klaster');
            $table->string('label_ukuran')->nullable()->after('label_prediksi');
            $table->string('label_kualitas')->nullable()->after('label_ukuran');
            $table->string('label_tampilan')->nullable()->after('label_kualitas');
        });
    }

    public function down(): void
    {
        Schema::table('data_klaster', function (Blueprint $table) {
            $table->dropColumn([
                'label_prediksi',
                'label_ukuran',
                'label_kualitas',
                'label_tampilan',
            ]);
        });
    }
};

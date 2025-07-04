<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropColumn('data_id');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->unsignedBigInteger('data_id')->nullable(); // atau sesuaikan dengan sebelumnya
        });
    }
};

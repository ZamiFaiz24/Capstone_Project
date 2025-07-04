<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_id');
            $table->float('berat')->nullable();         // weight = berat
            $table->float('intensitas')->nullable();    // intensitas sensor
            $table->string('status')->nullable();       // status sensor atau data
            $table->timestamp('dibuat_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};

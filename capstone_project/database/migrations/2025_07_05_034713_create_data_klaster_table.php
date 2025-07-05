<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataKlasterTable extends Migration
{
    public function up()
    {
        Schema::create('data_klaster', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_id')->nullable(); // relasi opsional ke sensor_data
            $table->float('berat_telur');
            $table->float('intensitas')->nullable(); // bisa disesuaikan
            $table->string('klaster'); // hasil klaster, misal: "besar", "sedang", "kecil"
            $table->timestamp('waktu_klaster')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_klaster');
    }
}

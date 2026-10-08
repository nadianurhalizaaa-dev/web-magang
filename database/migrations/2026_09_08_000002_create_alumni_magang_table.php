<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumni_magang', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('asal_instansi');
            $table->string('jurusan');
            $table->string('periode_magang');
            $table->text('kesan_pesan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumni_magang');
    }
};

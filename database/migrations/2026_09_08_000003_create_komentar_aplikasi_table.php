<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomentarAplikasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('komentar_aplikasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pengguna');
            $table->string('email')->nullable();
            $table->tinyInteger('rating')->default(5);
            $table->text('komentar');
            $table->string('status')->default('published');
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
        Schema::dropIfExists('komentar_aplikasi');
    }
}

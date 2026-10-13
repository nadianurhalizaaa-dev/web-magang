<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id('id_penilaian');
            $table->unsignedBigInteger('id_laporan_magang');
            $table->unsignedBigInteger('id_pembimbing');
            $table->text('judul_nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal_nilai')->useCurrent();
            $table->string('poin_nilai', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_laporan_magang')
                  ->references('id_laporan')
                  ->on('laporan_magang')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};

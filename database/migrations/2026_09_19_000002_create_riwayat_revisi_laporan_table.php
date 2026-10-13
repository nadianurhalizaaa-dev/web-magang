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
        Schema::create('riwayat_revisi_laporan', function (Blueprint $table) {
            $table->id('id_revisi');
            $table->unsignedBigInteger('id_laporan');
            $table->string('link_google_doc', 255);
            $table->timestamp('tanggal_revisi')->useCurrent();
            $table->timestamps();

            $table->foreign('id_laporan')
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
        Schema::dropIfExists('riwayat_revisi_laporan');
    }
};

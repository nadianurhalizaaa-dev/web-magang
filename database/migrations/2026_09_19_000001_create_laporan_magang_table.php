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
        Schema::create('laporan_magang', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->unsignedBigInteger('id_peserta');
            $table->string('judul_laporan', 255);
            $table->enum('kategori_laporan', ['Individu', 'Kelompok']);
            $table->text('abstrak')->nullable();
            $table->string('file_laporan_pdf', 255);
            $table->timestamp('tgl_submit')->useCurrent();
            $table->enum('status_laporan', ['Draft', 'Menunggu Review', 'Revisi', 'Disetujui'])->default('Menunggu Review');
            $table->text('catatan_revisi')->nullable();
            $table->decimal('nilai_laporan', 5, 2)->nullable();
            $table->dateTime('tanggal_dinilai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_magang');
    }
};

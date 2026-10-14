<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmisiTugasTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('submisi_tugas')) {
            Schema::create('submisi_tugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('file_path');
                $table->text('catatan_peserta')->nullable();
                $table->dateTime('tanggal_submit');
                $table->integer('nilai')->nullable();
                $table->text('feedback')->nullable();
                $table->enum('status', ['belum_dinilai', 'sudah_dinilai'])->default('belum_dinilai');

                // Kolom penilai & waktu penilaian
                $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('tanggal_dinilai')->nullable();

                $table->timestamps();

                $table->unique(['tugas_id', 'user_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('submisi_tugas');
    }
}

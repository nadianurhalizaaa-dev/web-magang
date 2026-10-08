<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('magang_id')->nullable();
            $table->string('participant_code');
            $table->string('participant_name');
            $table->string('institution')->nullable();
            $table->date('tanggal');

            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            $table->enum('status', [
                'Hadir',
                'Tidak Hadir',
                'Izin',
                'Sakit'
            ])->default('Tidak Hadir');

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique([
                'participant_code',
                'tanggal'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};

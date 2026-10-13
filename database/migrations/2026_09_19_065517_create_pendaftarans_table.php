<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendaftaransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('periode_id')->nullable()->constrained('periode_magangs')->onDelete('set null');
            $table->foreignId('pembimbing_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('asal_instansi', 150);
            $table->string('file_surat_pengantar', 255);
            $table->string('status')->default('Calon Pemagang');
            $table->date('tanggal_daftar');
            $table->date('tanggal_selesai')->nullable();
            $table->string('file_surat_balasan', 255)->nullable();
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
        Schema::dropIfExists('pendaftarans');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropForeign(['id_laporan_magang']);
            $table->unsignedBigInteger('id_laporan_magang')->nullable()->change();
            $table->foreign('id_laporan_magang')
                ->references('id_laporan')
                ->on('laporan_magang')
                ->cascadeOnDelete();
            $table->foreignId('id_peserta')->nullable()->after('id_laporan_magang')
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('nilai_akhir', 5, 2)->nullable()->after('rincian_nilai');
        });
    }

    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropForeign(['id_peserta']);
            $table->dropForeign(['id_laporan_magang']);
            $table->foreignId('id_laporan_magang')->nullable(false)->change();
            $table->foreign('id_laporan_magang')
                ->references('id_laporan')
                ->on('laporan_magang')
                ->cascadeOnDelete();
            $table->dropColumn(['id_peserta', 'nilai_akhir']);
        });
    }
};

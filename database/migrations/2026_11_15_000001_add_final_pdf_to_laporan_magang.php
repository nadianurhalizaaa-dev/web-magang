<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_magang', function (Blueprint $table) {
            $table->enum('status_laporan', ['Draft', 'Menunggu Review', 'Revisi', 'Disetujui', 'Selesai'])->default('Menunggu Review')->change();
            $table->string('file_pdf_final')->nullable()->after('file_laporan_pdf');
        });

        DB::table('laporan_magang')
            ->where('status_laporan', 'Disetujui')
            ->whereNotNull('tanggal_dinilai')
            ->update(['status_laporan' => 'Selesai']);
    }

    public function down(): void
    {
        DB::table('laporan_magang')
            ->where('status_laporan', 'Selesai')
            ->update(['status_laporan' => 'Disetujui']);

        Schema::table('laporan_magang', function (Blueprint $table) {
            $table->dropColumn('file_pdf_final');
            $table->enum('status_laporan', ['Draft', 'Menunggu Review', 'Revisi', 'Disetujui'])->default('Menunggu Review')->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_magang', function (Blueprint $table) {
            $table->string('file_laporan_pdf', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_magang', function (Blueprint $table) {
            $table->string('file_laporan_pdf', 255)->nullable(false)->change();
        });
    }
};

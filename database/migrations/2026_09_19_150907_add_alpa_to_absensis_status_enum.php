<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status ENUM('Hadir', 'Tidak Hadir', 'Izin', 'Sakit', 'Alpa') DEFAULT 'Tidak Hadir'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status ENUM('Hadir', 'Tidak Hadir', 'Izin', 'Sakit') DEFAULT 'Tidak Hadir'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('magang_id')
                ->nullable()
                ->unique()
                ->constrained('magangs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['magang_id']);
            $table->dropUnique(['magang_id']);
            $table->dropColumn('magang_id');
        });
    }
};

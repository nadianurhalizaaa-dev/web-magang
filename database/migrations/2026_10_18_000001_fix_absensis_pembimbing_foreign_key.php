<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign('absensis_pembimbing_id_foreign');
            $table->foreign('pembimbing_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign('absensis_pembimbing_id_foreign');
            $table->foreign('pembimbing_id')
                ->references('id')
                ->on('pembimbings')
                ->nullOnDelete();
        });
    }
};

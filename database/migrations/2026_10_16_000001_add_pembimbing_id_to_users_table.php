<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pembimbing_id')) {
                $table->foreignId('pembimbing_id')->nullable()->after('password')->constrained('pembimbings')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pembimbing_id')) {
                $table->dropForeign(['pembimbing_id']);
                $table->dropColumn('pembimbing_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'nim')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('nim', 50)->nullable()->unique();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'nim')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_nim_unique');
            $table->dropColumn('nim');
        });
    }
};

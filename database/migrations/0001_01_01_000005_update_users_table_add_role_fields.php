<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $hasRole = Schema::hasColumn('users', 'role');
        $hasPhone = Schema::hasColumn('users', 'no_hp');
        $hasPhoto = Schema::hasColumn('users', 'foto');

        if ($hasRole && $hasPhone && $hasPhoto) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'pembimbing', 'peserta'])
                    ->default('peserta')
                    ->after('password');
            }

            if (!Schema::hasColumn('users', 'no_hp')) {
                $table->string('no_hp')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'foto')) {
                $table->string('foto')->nullable()->after('no_hp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Some fields may already exist before this migration runs. Keeping
        // them avoids deleting account data or breaking related features.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'program_studi')) {
                $table->string('program_studi', 150)->nullable();
            }

            if (! Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'program_studi')) {
                $columns[] = 'program_studi';
            }
            if (Schema::hasColumn('users', 'alamat')) {
                $columns[] = 'alamat';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

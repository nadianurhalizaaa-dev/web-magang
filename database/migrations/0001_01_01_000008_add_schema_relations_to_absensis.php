<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembimbings', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('no_hp', 20)->nullable();
            $table->string('bidang', 100)->nullable();
            $table->timestamps();
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('pembimbing_id')->nullable()->after('user_id')->constrained('pembimbings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['pembimbing_id']);
            $table->dropColumn(['user_id', 'pembimbing_id']);
        });

        Schema::dropIfExists('pembimbings');
    }
};

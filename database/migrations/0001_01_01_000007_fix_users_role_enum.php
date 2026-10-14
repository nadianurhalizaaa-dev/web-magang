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
        // Role fields and the supported role values are handled by the
        // additive migration 0001_01_01_000005. Never rebuild users here:
        // access tokens and related records depend on its primary keys.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This compatibility migration no longer changes the users schema.
    }
};

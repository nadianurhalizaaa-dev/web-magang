<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFotoToAktivitasMagangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('aktivitas_magang') && !Schema::hasColumn('aktivitas_magang', 'foto')) {
            Schema::table('aktivitas_magang', function (Blueprint $table) {
                $table->string('foto')->nullable()->after('lokasi');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aktivitas_magang', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
}

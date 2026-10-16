<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai spesifikasi ERD: Tabel Pembimbing
     * Id (BigInt PK), Nama (Varchar 100), Email (Varchar 100), No_hp (Varchar 20), Bidang (Varchar 100), Timestamps
     */
    public function up(): void
    {
        if (!Schema::hasTable('pembimbing')) {
            Schema::create('pembimbing', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('email', 100)->unique();
                $table->string('no_hp', 20)->nullable();
                $table->string('bidang', 100)->nullable();
                $table->timestamps();
            });

            // Sinkronkan data jika sebelumnya ada di pembimbings
            if (Schema::hasTable('pembimbings')) {
                $existing = DB::table('pembimbings')->get();
                foreach ($existing as $row) {
                    DB::table('pembimbing')->updateOrInsert(
                        ['id' => $row->id],
                        [
                            'nama' => $row->nama,
                            'email' => $row->email,
                            'no_hp' => $row->no_hp,
                            'bidang' => $row->bidang,
                            'created_at' => $row->created_at,
                            'updated_at' => $row->updated_at,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembimbing');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $oldCode = '201013021';
            $correctCode = '231013021';
            $magang = DB::table('magangs')->where('participant_code', $oldCode)->first();

            if (! $magang) {
                return;
            }

            if (DB::table('magangs')->where('participant_code', $correctCode)->exists()) {
                throw new RuntimeException('Both the old and corrected Widya participant codes exist; resolve the duplicate before migrating.');
            }

            if (DB::table('absensis')->where('participant_code', $oldCode)->exists()
                && DB::table('absensis')->where('participant_code', $correctCode)->exists()) {
                throw new RuntimeException('Attendance records exist for both Widya participant codes; resolve the duplicate before migrating.');
            }

            DB::table('absensis')
                ->where('participant_code', $oldCode)
                ->update(['participant_code' => $correctCode]);

            DB::table('magangs')
                ->where('id', $magang->id)
                ->update(['participant_code' => $correctCode]);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $oldCode = '201013021';
            $correctCode = '231013021';
            $magang = DB::table('magangs')->where('participant_code', $correctCode)->first();

            if (! $magang) {
                return;
            }

            if (DB::table('magangs')->where('participant_code', $oldCode)->exists()) {
                throw new RuntimeException('The old Widya participant code is already in use; cannot safely roll back.');
            }

            DB::table('absensis')
                ->where('participant_code', $correctCode)
                ->update(['participant_code' => $oldCode]);

            DB::table('magangs')
                ->where('id', $magang->id)
                ->update(['participant_code' => $oldCode]);
        });
    }
};

<?php

namespace Database\Seeders;

use App\Models\Pembimbing;
use Illuminate\Database\Seeder;

class PembimbingSeeder extends Seeder
{
    public function run(): void
    {
        Pembimbing::firstOrCreate(
            ['email' => 'pembimbing@absensi.com'],
            [
                'nama' => 'Pembimbing Magang',
                'no_hp' => '081234567891',
                'bidang' => 'Pembimbing Lapangan',
            ]
        );
    }
}

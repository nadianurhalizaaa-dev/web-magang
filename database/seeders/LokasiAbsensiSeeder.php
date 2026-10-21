<?php

namespace Database\Seeders;

use App\Models\LokasiAbsensi;
use Illuminate\Database\Seeder;

class LokasiAbsensiSeeder extends Seeder
{
    public function run(): void
    {
        LokasiAbsensi::firstOrCreate(
            ['nama_lokasi' => 'Kantor Utama / Kantor Magang'],
            [
                'alamat' => 'Jl. Merdeka No. 45, Gedung Utama Lt. 2',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meter' => 100,
                'is_active' => true,
            ]
        );
    }
}

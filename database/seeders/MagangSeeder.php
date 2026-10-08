<?php

namespace Database\Seeders;

use App\Models\Magang;
use Illuminate\Database\Seeder;

class MagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'participant_code' => '231013018',
                'participant_name' => 'Hanifah Khairunnisa',
                'institution' => 'Sekolah Tinggi Teknologi Payakumbuh',
                'status' => 'Aktif',
            ],
            [
                'participant_code' => '23013008',
                'participant_name' => 'Nadia Nurhaliza',
                'institution' => 'Sekolah Tinggi Teknologi Payakumbuh',
                'status' => 'Aktif',
            ],
            [
                'participant_code' => '201013021',
                'participant_name' => 'Widya Anjelina',
                'institution' => 'Sekolah Tinggi Teknologi Payakumbuh',
                'status' => 'Aktif',
            ],
            [
                'participant_code' => '231013027',
                'participant_name' => 'Tiara Agus Fajri',
                'institution' => 'Sekolah Tinggi Teknologi Payakumbuh',
                'status' => 'Aktif',
            ],
            [
                'participant_code' => '231013002',
                'participant_name' => 'Anisa Fitriani',
                'institution' => 'Sekolah Tinggi Teknologi Payakumbuh',
                'status' => 'Aktif',
            ],
        ];

        foreach ($items as $item) {
            Magang::firstOrCreate(
                ['participant_code' => $item['participant_code']],
                [
                    'participant_name' => $item['participant_name'],
                    'institution' => $item['institution'],
                    'status' => $item['status'],
                ]
            );
        }
    }
}

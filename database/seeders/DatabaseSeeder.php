<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'pembimbing@absensi.com'],
            [
                'name' => 'Pembimbing Magang',
                'password' => bcrypt('pembimbing123'),
                'role' => 'pembimbing',
                'no_hp' => '081234567891',
                'foto' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'peserta@absensi.com'],
            [
                'name' => 'Peserta Magang',
                'password' => bcrypt('peserta123'),
                'role' => 'peserta',
                'no_hp' => '081234567892',
                'foto' => null,
            ]
        );

        $this->call(PembimbingSeeder::class);
        $this->call(MagangSeeder::class);
    }
}

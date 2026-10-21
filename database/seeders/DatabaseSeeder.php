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
        $admin = User::firstOrCreate(
            ['email' => 'admin@absensi.com'],
            [
                'name' => 'admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'no_hp' => '081234567892',
                'foto' => null,
            ]
        );

        $peserta = User::firstOrCreate(
            ['email' => 'peserta@absensi.com'],
            [
                'name' => 'Peserta Magang',
                'password' => bcrypt('peserta123'),
                'role' => 'peserta',
                'no_hp' => '081234567892',
                'foto' => null,
            ]
        );



        $demoProfile = [
            'nim' => 'DEMO-' . str_pad((string) $peserta->id, 6, '0', STR_PAD_LEFT),
            'program_studi' => 'Contoh Program Studi (akun demo)',
            'alamat' => 'Alamat contoh untuk akun demo - bukan data nyata.',
        ];

        foreach ($demoProfile as $field => $value) {
            if (blank($peserta->{$field})) {
                $peserta->{$field} = $value;
            }
        }

        if ($peserta->isDirty()) {
            $peserta->save();
        }

        $this->call(PembimbingSeeder::class);
        $this->call(MagangSeeder::class);
        $this->call(LokasiAbsensiSeeder::class);
        $this->call([
            BerandaSeeder::class,
            LaporanMagangSeeder::class,
        ]);
    }
}

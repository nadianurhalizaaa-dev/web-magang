<?php

namespace Database\Seeders;

use App\Models\AlumniMagang;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BerandaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 0. Seed Default Admin User
        User::updateOrCreate(
            ['email' => 'admin@magang.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 1. Seed Company Profile
        CompanyProfile::updateOrCreate(
            ['id' => 1],
            [
                'nama_perusahaan' => 'PT Solusi Teknologi Nusantara',
                'deskripsi_singkat' => 'Perusahaan IT terdepan yang bergerak di bidang riset, konsultasi digital, dan pengembangan perangkat lunak skala enterprise.',
                'tentang_kantor' => 'PT Solusi Teknologi Nusantara didirikan pada tahun 2018 sebagai pusat inovasi dan transformasi digital. Kantor kami dirancang modern dengan kultur kerja kolaboratif, terbuka, dan mendukung pembelajaran berkelanjutan. Melalui program manajemen magang kami, peserta dilibatkan secara langsung dalam proyek nyata industri dengan bimbingan mentor senior.',
                'bidang_usaha' => 'Teknologi Informasi, Software Development, & Digital Transformation',
                'visi' => 'Menjadi pelopor solusi teknologi terdepan di Indonesia serta wadah pencetak talenta digital berstandar global.',
                'misi' => "1. Mengembangkan produk perangkat lunak berkualitas tinggi.\n2. Menyediakan ekosistem magang yang inovatif dan terstruktur.\n3. Membimbing mahasiswa & lulusan muda menjadi profesional siap kerja.",
                'alamat' => 'Gedung Cyber Tower Lt. 8, Jl. H.R. Rasuna Said No. 12, Jakarta Selatan, 12940',
                'email' => 'internship@solusitek.co.id',
                'telepon' => '(021) 5290-8888',
                'website' => 'https://solusitek.co.id',
            ]
        );

        // 2. Seed Alumni Magang (Kesan & Pesan)
        $alumniData = [
            [
                'nama' => 'Budi Santoso',
                'asal_instansi' => 'Universitas Indonesia',
                'jurusan' => 'Teknik Informatika',
                'periode_magang' => 'Batch 1 - 2025',
                'kesan_pesan' => 'Kesan: Pengalaman magang yang sangat berharga dan berkesan. Lingkungan kerja sangat suportif serta dibimbing langsung oleh mentor berpengalaman.\nPesan: Semoga program magang ini terus berlanjut dan mencetak lebih banyak talenta muda hebat di bidang IT.',
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'asal_instansi' => 'Institut Teknologi Bandung',
                'jurusan' => 'Sistem Informasi',
                'periode_magang' => 'Batch 1 - 2025',
                'kesan_pesan' => 'Kesan: Belajar banyak hal baru mengenai manajemen proyek software dan kerja tim profesional yang belum pernah didapatkan di bangku kuliah.\nPesan: Pertahankan kultur kerja yang ramah dan inklusif bagi para peserta magang.',
            ],
            [
                'nama' => 'Rizky Pratama',
                'asal_instansi' => 'Universitas Gadjah Mada',
                'jurusan' => 'Ilmu Komputer',
                'periode_magang' => 'Batch 2 - 2024',
                'kesan_pesan' => 'Kesan: Program magang terstruktur dengan fasilitas yang memadai. Proyek nyata yang diberikan benar-benar mengasah skill problem solving.\nPesan: Tetap pertahankan kualitas mentoring dan kolaborasi yang solid antar divisi.',
            ],
            [
                'nama' => 'Anisa Rahmawati',
                'asal_instansi' => 'Telkom University',
                'jurusan' => 'Teknologi Informasi',
                'periode_magang' => 'Batch 2 - 2024',
                'kesan_pesan' => 'Kesan: Mentor sangat terbuka untuk berdiskusi dan memberikan arahan mendalam. Saya merasa sangat terbantu dalam mengembangkan karir.\nPesan: Sukses selalu untuk PT Solusi Teknologi Nusantara!',
            ],
            [
                'nama' => 'Deden Kurniawan',
                'asal_instansi' => 'Politeknik Negeri Bandung',
                'jurusan' => 'Teknik Komputer',
                'periode_magang' => 'Batch 1 - 2024',
                'kesan_pesan' => 'Kesan: Atmosfer kerja modern dan kekeluargaan membuat proses belajar terasa menyenangkan tanpa tekanan berlebih.\nPesan: Semoga ke depan durasi magang bisa diperpanjang agar materi yang dipelajari semakin luas.',
            ],
            [
                'nama' => 'Maya Indah',
                'asal_instansi' => 'Universitas Brawijaya',
                'jurusan' => 'Sistem Informasi',
                'periode_magang' => 'Batch 1 - 2024',
                'kesan_pesan' => 'Kesan: Pengalaman luar biasa bisa ikut serta dalam pengembangan produk skala besar secara langsung.\nPesan: Semoga perusahaan makin sukses dan jaya selalu!',
            ],
        ];

        foreach ($alumniData as $alumni) {
            AlumniMagang::updateOrCreate(
                ['nama' => $alumni['nama']],
                $alumni
            );
        }
    }
}

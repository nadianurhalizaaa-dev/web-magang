<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LaporanMagang;
use App\Models\Penilaian;
use App\Models\RiwayatRevisiLaporan;

class LaporanMagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $l1 = LaporanMagang::create([
            'id_peserta' => 1,
            'judul_laporan' => 'Pengembangan Portal E-Government Berbasis Laravel & React',
            'kategori_laporan' => 'Individu',
            'abstrak' => 'Laporan akhir mengenai implementasi sistem informasi manajemen magang enterprise.',
            'file_laporan_pdf' => 'laporan_budi_santoso_final.pdf',
            'tgl_submit' => '2026-09-10 09:30:00',
            'status_laporan' => 'Disetujui',
            'catatan_revisi' => 'Struktur bab 3 sudah diperbaiki sesuai standar format kantor.',
            'nilai_laporan' => 92.50,
            'tanggal_dinilai' => '2026-09-15 14:00:00',
        ]);

        Penilaian::create([
            'id_laporan_magang' => $l1->id_laporan,
            'id_pembimbing' => 101,
            'judul_nilai' => 'Penilaian Akhir Laporan Magang',
            'catatan' => 'Sangat memuaskan, dokumentasi dan hasil implementasi aplikasi luar biasa.',
            'tanggal_nilai' => '2026-09-15 14:00:00',
            'poin_nilai' => 'A (Sangat Baik)',
        ]);

        $l2 = LaporanMagang::create([
            'id_peserta' => 2,
            'judul_laporan' => 'Optimasi Performa Query Database & Rest API Portal Publik',
            'kategori_laporan' => 'Kelompok',
            'abstrak' => 'Analisis dan tuning performa database MySQL pada server produksi magang.',
            'file_laporan_pdf' => 'laporan_kelompok_siti_nurhaliza.pdf',
            'tgl_submit' => '2026-09-12 11:15:00',
            'status_laporan' => 'Menunggu Review',
            'catatan_revisi' => null,
            'nilai_laporan' => null,
            'tanggal_dinilai' => null,
        ]);

        $l3 = LaporanMagang::create([
            'id_peserta' => 3,
            'judul_laporan' => 'Rancang Bangun Microservice Authentication & Dashboard Analytics',
            'kategori_laporan' => 'Individu',
            'abstrak' => 'Perancangan arsitektur microservices untuk modul autentikasi JWT.',
            'file_laporan_pdf' => 'laporan_rizky_pratama_draft.pdf',
            'tgl_submit' => '2026-09-14 16:45:00',
            'status_laporan' => 'Revisi',
            'catatan_revisi' => 'Tolong tambahkan diagram ERD pada Lampiran 2 dan tautkan Google Docs revisinya.',
            'nilai_laporan' => null,
            'tanggal_dinilai' => null,
        ]);

        RiwayatRevisiLaporan::create([
            'id_laporan' => $l3->id_laporan,
            'link_google_doc' => 'https://docs.google.com/document/d/1example_revisi_rizky/edit',
            'tanggal_revisi' => '2026-09-16 10:00:00',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RekapitulasiController extends Controller
{
    /**
     * UC-01: Memeriksa Rekapitulasi Tugas & Absensi (Anak Magang & Pembimbing)
     */
    public function index(Request $request)
    {
        // Mock data representation for tasks & attendance recapitulation
        $rekapitulasi = [
            [
                'id_peserta' => 1,
                'nama_peserta' => 'Budi Santoso',
                'instansi' => 'Universitas Indonesia',
                'jurusan' => 'Teknik Informatika',
                'total_absensi' => '98%',
                'tugas_selesai' => 12,
                'total_tugas' => 12,
                'projek' => 'Sistem Informasi Manajemen Magang',
                'lampiran_tgl_projek' => '2026-09-01 s/d 2026-09-18',
                'status_kehadiran' => 'Hadir Lengkap'
            ],
            [
                'id_peserta' => 2,
                'nama_peserta' => 'Siti Nurhaliza',
                'instansi' => 'Institut Teknologi Bandung',
                'jurusan' => 'Sistem Informasi',
                'total_absensi' => '95%',
                'tugas_selesai' => 10,
                'total_tugas' => 11,
                'projek' => 'Portal Layanan Publik & Dashboard Evaluasi',
                'lampiran_tgl_projek' => '2026-09-05 s/d 2026-09-19',
                'status_kehadiran' => 'Izin 1 Hari'
            ],
            [
                'id_peserta' => 3,
                'nama_peserta' => 'Rizky Pratama',
                'instansi' => 'Universitas Gadjah Mada',
                'jurusan' => 'Ilmu Komputer',
                'total_absensi' => '100%',
                'tugas_selesai' => 15,
                'total_tugas' => 15,
                'projek' => 'Rest API Gateway & Microservices DB',
                'lampiran_tgl_projek' => '2026-08-15 s/d 2026-09-15',
                'status_kehadiran' => 'Hadir Lengkap'
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data rekapitulasi tugas & absensi berhasil dimuat',
            'data' => $rekapitulasi
        ]);
    }
}

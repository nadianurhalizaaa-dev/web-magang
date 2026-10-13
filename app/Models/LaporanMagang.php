<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanMagang extends Model
{
    use HasFactory;

    protected $table = 'laporan_magang';
    protected $primaryKey = 'id_laporan';

    protected $fillable = [
        'id_peserta',
        'judul_laporan',
        'kategori_laporan',
        'abstrak',
        'file_laporan_pdf',
        'tgl_submit',
        'status_laporan',
        'catatan_revisi',
        'nilai_laporan',
        'tanggal_dinilai',
    ];

    protected $casts = [
        'tgl_submit' => 'datetime',
        'tanggal_dinilai' => 'datetime',
        'nilai_laporan' => 'float',
    ];

    public function riwayatRevisi()
    {
        return $this->hasMany(RiwayatRevisiLaporan::class, 'id_laporan', 'id_laporan')->orderByDesc('id_revisi');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'id_laporan_magang', 'id_laporan')->orderByDesc('id_penilaian');
    }
}

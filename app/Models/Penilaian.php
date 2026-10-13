<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';
    protected $primaryKey = 'id_penilaian';

    protected $fillable = [
        'id_laporan_magang',
        'id_pembimbing',
        'judul_nilai',
        'catatan',
        'tanggal_nilai',
        'poin_nilai',
    ];

    protected $casts = [
        'tanggal_nilai' => 'datetime',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanMagang::class, 'id_laporan_magang', 'id_laporan');
    }
}

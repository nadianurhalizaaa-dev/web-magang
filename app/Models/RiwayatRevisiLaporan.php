<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatRevisiLaporan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_revisi_laporan';
    protected $primaryKey = 'id_revisi';

    protected $fillable = [
        'id_laporan',
        'link_google_doc',
        'tanggal_revisi',
    ];

    protected $casts = [
        'tanggal_revisi' => 'datetime',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanMagang::class, 'id_laporan', 'id_laporan');
    }
}

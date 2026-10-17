<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'periode_id',
        'pembimbing_id',
        'asal_instansi',
        'file_surat_pengantar',
        'status',
        'tanggal_daftar',
        'tanggal_selesai',
        'file_surat_balasan',
        'surat_balasan_diterima_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeMagang::class, 'periode_id');
    }

    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'pembimbing_id');
    }
}

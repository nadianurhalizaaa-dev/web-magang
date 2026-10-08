<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmisiTugas extends Model
{
    use HasFactory;

    protected $table = 'submisi_tugas';

    protected $fillable = [
        'tugas_id',
        'user_id',
        'file_path',
        'catatan_peserta',
        'tanggal_submit',
        'nilai',
        'feedback',
        'status',
        'dinilai_oleh',
        'tanggal_dinilai',
    ];

    protected $casts = [
        'tanggal_submit'  => 'datetime',
        'tanggal_dinilai' => 'datetime',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function peserta()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}

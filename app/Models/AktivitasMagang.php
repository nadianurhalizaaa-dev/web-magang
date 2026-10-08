<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasMagang extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_magang';

    protected $fillable = [
        'user_id',
        'judul',
        'deskripsi',
        'tanggal',
        'kategori',
        'lokasi',
        'foto',
        'status',
    ];

    protected $appends = ['foto_url'];

    /**
     * Accessor untuk mendapatkan URL lengkap foto aktivitas.
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset($this->foto);
        }
        return null;
    }

    /**
     * Relasi ke Admin/User yang menginput aktivitas magang.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke daftar komentar pengguna pada aktivitas magang ini.
     */
    public function komentar()
    {
        return $this->hasMany(KomentarAktivitas::class, 'aktivitas_magang_id')->latest();
    }
}

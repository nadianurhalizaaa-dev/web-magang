<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomentarAktivitas extends Model
{
    use HasFactory;

    protected $table = 'komentar_aktivitas';

    protected $fillable = [
        'aktivitas_magang_id',
        'user_id',
        'komentar',
    ];

    /**
     * Relasi ke Aktivitas Magang yang dikomentari.
     */
    public function aktivitas()
    {
        return $this->belongsTo(AktivitasMagang::class, 'aktivitas_magang_id');
    }

    /**
     * Relasi ke Pengguna/User yang menginput komentar.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomentarAplikasi extends Model
{
    use HasFactory;

    protected $table = 'komentar_aplikasi';

    protected $fillable = [
        'user_id',
        'nama_pengguna',
        'email',
        'rating',
        'komentar',
        'status',
    ];

    /**
     * Relasi ke Pengguna/User yang mengirimkan komentar ulasan aplikasi.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

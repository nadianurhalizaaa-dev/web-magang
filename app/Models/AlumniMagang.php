<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniMagang extends Model
{
    use HasFactory;

    protected $table = 'alumni_magang';

    protected $fillable = [
        'nama',
        'asal_instansi',
        'jurusan',
        'periode_magang',
        'kesan_pesan',
    ];
}

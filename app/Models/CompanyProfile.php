<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $table = 'company_profiles';

    protected $fillable = [
        'nama_perusahaan',
        'deskripsi_singkat',
        'tentang_kantor',
        'bidang_usaha',
        'visi',
        'misi',
        'alamat',
        'email',
        'telepon',
        'website',
    ];
}

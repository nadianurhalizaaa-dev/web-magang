<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'judul',
        'deskripsi',
        'deadline',
        'created_by',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function submisi()
    {
        return $this->hasMany(SubmisiTugas::class, 'tugas_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

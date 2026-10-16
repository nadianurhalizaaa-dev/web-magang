<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $fillable = [
        'user_id',
        'pembimbing_id',
        'magang_id',
        'participant_code',
        'participant_name',
        'institution',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'keterangan',
        'foto',
    ];

    protected $appends = [
        'foto_url',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? '/storage/' . $this->foto : null;
    }

    public function magang()
    {
        return $this->belongsTo(Magang::class, 'participant_code', 'participant_code');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembimbing extends Model
{
    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'bidang',
    ];

    public function magangs(): HasMany
    {
        return $this->hasMany(Magang::class);
    }
}

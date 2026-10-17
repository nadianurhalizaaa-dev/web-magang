<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Pembimbing extends Model
{
    /**
     * Sesuai relasi database (ERD): Tabel Pembimbing
     * Kolom: id, nama, email, no_hp, bidang, created_at, updated_at
     */
    protected $table = 'pembimbing';

    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'bidang',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Fallback jika hanya tabel pembimbings yang tersedia
        if (!Schema::hasTable('pembimbing') && Schema::hasTable('pembimbings')) {
            $this->setTable('pembimbings');
        }
    }

    protected static function booted(): void
    {
        // Menjaga sinkronisasi data antar tabel pembimbing & pembimbings
        static::saved(function ($model) {
            $altTable = $model->getTable() === 'pembimbing' ? 'pembimbings' : 'pembimbing';
            if (Schema::hasTable($altTable)) {
                DB::table($altTable)->updateOrInsert(
                    ['id' => $model->id],
                    [
                        'nama' => $model->nama,
                        'email' => $model->email,
                        'no_hp' => $model->no_hp,
                        'bidang' => $model->bidang,
                        'created_at' => $model->created_at,
                        'updated_at' => $model->updated_at,
                    ]
                );
            }
        });

        static::deleted(function ($model) {
            $altTable = $model->getTable() === 'pembimbing' ? 'pembimbings' : 'pembimbing';
            if (Schema::hasTable($altTable)) {
                DB::table($altTable)->where('id', $model->id)->delete();
            }
        });
    }

    public function magangs(): HasMany
    {
        return $this->hasMany(Magang::class, 'pembimbing_id');
    }
}

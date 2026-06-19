<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role lokal Service B (skema hak akses lokal).
 *
 * JWT dari Cloud Dosen tidak mengenal role internal kita; RoleMapper
 * menerjemahkan klaim eksternal menjadi salah satu role di tabel ini.
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}

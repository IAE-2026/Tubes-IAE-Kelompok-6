<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * User federasi dari SSO Cloud Dosen.
 *
 * User tidak dibuat lewat registrasi lokal; mereka "dipetakan" (provisioned)
 * dari payload JWT yang diterbitkan server SSO dosen. Kolom iae_subject
 * menyimpan klaim `sub` JWT sebagai identitas global yang unik.
 */
class User extends Authenticatable
{
    protected $fillable = [
        'iae_subject',
        'name',
        'email',
        'token_type',
        'last_login_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'remember_token',
    ];

    /**
     * Role lokal yang dipetakan untuk user federasi ini.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $name): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->name === $name);
    }

    public function hasAnyRole(array $names): bool
    {
        return $this->roles->contains(fn (Role $role) => in_array($role->name, $names, true));
    }

    /**
     * Nama role utama (pertama) untuk keperluan tampilan/log.
     */
    public function primaryRole(): ?string
    {
        return optional($this->roles->first())->name;
    }
}

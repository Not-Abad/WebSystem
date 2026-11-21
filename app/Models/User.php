<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_rol',
        'username',
        'password_hash',
        'is_active',
        'intentos_fallidos',
        'bloqueado_hasta',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'two_factor_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'intentos_fallidos' => 'integer',
            'bloqueado_hasta' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Laravel espera el campo "password", pero aquí se llama "password_hash".
     */
    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function administrador(): HasOne
    {
        return $this->hasOne(Administrador::class, 'id_usuario', 'id_usuario');
    }

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'id_usuario', 'id_usuario');
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(Alumno::class, 'id_usuario', 'id_usuario');
    }

    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Nombre legible según el perfil vinculado al rol.
     */
    public function nombreCompleto(): string
    {
        $perfil = match ($this->id_rol) {
            Rol::ADMINISTRADOR => $this->administrador,
            Rol::DOCENTE => $this->docente,
            Rol::ALUMNO => $this->alumno,
            Rol::TUTOR => $this->tutor,
            default => null,
        };

        if ($perfil && $perfil->nombre) {
            return trim($perfil->nombre.' '.$perfil->apellido_paterno.' '.$perfil->apellido_materno);
        }

        return $this->username;
    }

    public function esRol(int|string $rol): bool
    {
        if (is_int($rol)) {
            return $this->id_rol === $rol;
        }

        $nombre = $this->rol?->nombre;

        return $nombre !== null && strcasecmp($nombre, $rol) === 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    public const SUPERUSUARIO = 1;
    public const ADMINISTRADOR = 2;
    public const DOCENTE = 3;
    public const ALUMNO = 4;
    public const TUTOR = 5;

    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol', 'id_rol');
    }
}

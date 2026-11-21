<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoAlumno extends Model
{
    public const PREINSCRITO = 1;
    public const ACTIVO = 2;
    public const BAJA_TEMPORAL = 3;
    public const BAJA_DEFINITIVA = 4;
    public const GRADUADO = 5;

    protected $table = 'estados_alumno';
    protected $primaryKey = 'id_estado_alumno';
    public $timestamps = false;

    protected $fillable = ['nombre'];
}

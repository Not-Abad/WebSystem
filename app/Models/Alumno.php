<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'id_usuario',
        'id_tutor',
        'id_estado_alumno',
        'id_plan',
        'id_semestre',
        'id_capacitacion',
        'id_area_propedeutica',
        'matricula',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'correo',
        'promedio_general',
    ];
}

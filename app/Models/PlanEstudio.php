<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanEstudio extends Model
{
    protected $table = 'planes_estudio';
    protected $primaryKey = 'id_plan';
    public $timestamps = false;

    protected $fillable = ['clave', 'nombre', 'anio_inicio', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

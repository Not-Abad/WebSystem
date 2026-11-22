<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanEstudio extends Model
{
    protected $table = 'planes_estudio';
    protected $primaryKey = 'id_plan';
    public $timestamps = false;

    protected $fillable = ['clave', 'nombre', 'anio_inicio', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'id_plan', 'id_plan');
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'id_plan', 'id_plan');
    }

    public function etiqueta(): string
    {
        return trim($this->clave.' · '.$this->nombre);
    }
}

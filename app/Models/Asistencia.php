<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    public const PRESENTE = 'presente';
    public const FALTA = 'falta';
    public const RETARDO = 'retardo';
    public const JUSTIFICADA = 'justificada';

    protected $table = 'asistencias';
    protected $primaryKey = 'id_asistencia';

    protected $fillable = [
        'id_clase',
        'id_alumno',
        'fecha',
        'parcial',
        'estatus',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'parcial' => 'integer',
    ];

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class, 'id_clase', 'id_clase');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AlumnoTutor extends Pivot
{
    public const MAXIMO_TUTORES_POR_ALUMNO = 3;

    protected $table = 'alumno_tutores';
    protected $primaryKey = 'id_alumno_tutor';
    public $incrementing = true;

    protected $fillable = [
        'id_alumno',
        'id_tutor',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = [
        'id_clase',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'aula',
        'id_salon',
    ];

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class, 'id_clase', 'id_clase');
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class, 'id_salon', 'id_salon');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Noticia extends Model
{
    protected $table = 'noticias';
    protected $primaryKey = 'id_noticia';

    protected $fillable = [
        'id_administrador',
        'titulo',
        'contenido',
        'imagen_portada_ruta',
        'fecha_publicacion',
        'is_active',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'id_administrador', 'id_administrador');
    }
}

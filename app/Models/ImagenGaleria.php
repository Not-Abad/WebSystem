<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenGaleria extends Model
{
    protected $table = 'imagenes_galeria';
    protected $primaryKey = 'id_imagen';

    protected $fillable = [
        'id_administrador',
        'titulo',
        'descripcion',
        'imagen_ruta',
        'fecha_subida',
        'is_active',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'id_administrador', 'id_administrador');
    }
}

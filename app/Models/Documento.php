<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    protected $table = 'documentos';
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'id_administrador',
        'titulo',
        'categoria',
        'archivo_ruta',
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

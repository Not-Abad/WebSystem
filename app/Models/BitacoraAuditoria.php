<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraAuditoria extends Model
{
    protected $table = 'bitacora_auditoria';
    protected $primaryKey = 'id_bitacora';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'tabla_afectada',
        'id_registro_afectado',
        'accion',
        'descripcion',
        'ip_origen',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}

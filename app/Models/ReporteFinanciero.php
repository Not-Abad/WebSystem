<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteFinanciero extends Model
{
    protected $table = 'reportes_financieros';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_administrador',
        'titulo',
        'mes',
        'anio',
        'archivo_pdf_ruta',
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

    public function periodo(): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return ($meses[$this->mes] ?? $this->mes).' '.$this->anio;
    }
}

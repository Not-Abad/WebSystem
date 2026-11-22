<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $table = 'docentes';
    protected $primaryKey = 'id_docente';

    protected $fillable = [
        'id_usuario',
        'id_capacitacion',
        'id_area_propedeutica',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'rfc',
        'matricula_rfc',
        'correo_institucional',
        'horas_semanales',
        'licenciatura',
        'especialidad',
        'maestria',
        'doctorado',
        'numero_personal_sep',
        'contacto',
        'nombramiento',
        'categoria',
        'plaza',
        'sindicato',
        'titulo',
        'cedula_profesional',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'horas_semanales' => 'integer',
    ];

    /**
     * Los perfiles anteriores guardaban el RFC en matricula_rfc.
     */
    public function getRfcAttribute(?string $value): string
    {
        return mb_strtoupper(trim((string) ($value ?: $this->matricula_rfc)));
    }

    /**
     * @return array<int, string>
     */
    public function formacionAcademica(): array
    {
        return array_values(array_filter([
            $this->licenciatura ? 'Licenciatura: '.$this->licenciatura : null,
            $this->especialidad ? 'Especialidad: '.$this->especialidad : null,
            $this->maestria ? 'Maestría: '.$this->maestria : null,
            $this->doctorado ? 'Doctorado: '.$this->doctorado : null,
        ]));
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaPropedeutica::class, 'id_area_propedeutica', 'id_area_propedeutica');
    }

    public function clases(): HasMany
    {
        return $this->hasMany(Clase::class, 'id_docente', 'id_docente');
    }

    public function reincoExportaciones(): HasMany
    {
        return $this->hasMany(ReincoExportacion::class, 'id_docente', 'id_docente');
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombre.' '.$this->apellido_paterno.' '.$this->apellido_materno);
    }
}

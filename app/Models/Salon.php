<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salon extends Model
{
    protected $table = 'salones';
    protected $primaryKey = 'id_salon';
    public $timestamps = false;

    protected $fillable = [
        'clave',
        'nombre',
        'capacidad',
        'edificio',
        'is_active',
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'is_active' => 'boolean',
    ];

    public function etiqueta(): string
    {
        return trim($this->clave.' · '.$this->nombre);
    }
}

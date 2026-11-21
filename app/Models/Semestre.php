<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    protected $table = 'semestres';
    protected $primaryKey = 'id_semestre';
    public $timestamps = false;

    protected $fillable = ['numero_semestre', 'nombre'];

    public function nombreCompleto(): string
    {
        return $this->numero_semestre.'. '.$this->nombre;
    }
}

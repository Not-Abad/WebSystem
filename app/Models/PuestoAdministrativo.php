<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuestoAdministrativo extends Model
{
    protected $table = 'puestos_administrativos';
    protected $primaryKey = 'id_puesto';
    public $timestamps = false;

    protected $fillable = ['nombre'];
}

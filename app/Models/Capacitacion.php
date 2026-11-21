<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capacitacion extends Model
{
    protected $table = 'capacitaciones';
    protected $primaryKey = 'id_capacitacion';
    public $timestamps = false;

    protected $fillable = ['nombre', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

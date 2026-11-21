<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaPropedeutica extends Model
{
    protected $table = 'areas_propedeuticas';
    protected $primaryKey = 'id_area_propedeutica';

    protected $fillable = ['nombre', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

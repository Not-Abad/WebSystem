<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('estados_alumno')->insertOrIgnore([
            'id_estado_alumno' => 6,
            'nombre' => 'reinscripcion',
        ]);
    }

    public function down(): void
    {
        DB::table('estados_alumno')->where('id_estado_alumno', 6)->delete();
    }
};

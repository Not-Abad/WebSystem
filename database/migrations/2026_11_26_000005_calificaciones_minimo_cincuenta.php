<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El porcentaje obtenido en cada criterio nunca puede ser menor a 50 %.
     * Los pesos de los criterios no se modifican.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `calificaciones` ADD CONSTRAINT `chk_calificaciones_porcentaje_minimo` CHECK (`porcentaje_obtenido` IS NULL OR `porcentaje_obtenido` >= 50.00)');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `calificaciones` DROP CONSTRAINT `chk_calificaciones_porcentaje_minimo`');
    }
};

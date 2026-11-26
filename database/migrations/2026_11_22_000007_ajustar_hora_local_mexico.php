<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La aplicación dejó de usar UTC y ahora muestra la hora de Xalapa,
     * Veracruz (America/Mexico_City, UTC-6 desde 2023).
     *
     * Los valores generados con now() antes de este cambio quedaron
     * guardados en UTC, así que se convierten a hora local. No se tocan
     * fechas capturadas manualmente (publicaciones, documentos, etc.),
     * porque esas ya se mostraban con la hora local escrita por el usuario.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach (Schema::getTables() as $tabla) {
            $nombreTabla = $tabla['name'];

            foreach (['created_at', 'updated_at'] as $columna) {
                if (Schema::hasColumn($nombreTabla, $columna)) {
                    $this->convertir($nombreTabla, $columna, $driver, false);
                }
            }
        }

        if (Schema::hasTable('aceptaciones_criterios')
            && Schema::hasColumn('aceptaciones_criterios', 'fecha_aceptacion')) {
            $this->convertir('aceptaciones_criterios', 'fecha_aceptacion', $driver, false);
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach (Schema::getTables() as $tabla) {
            $nombreTabla = $tabla['name'];

            foreach (['created_at', 'updated_at'] as $columna) {
                if (Schema::hasColumn($nombreTabla, $columna)) {
                    $this->convertir($nombreTabla, $columna, $driver, true);
                }
            }
        }

        if (Schema::hasTable('aceptaciones_criterios')
            && Schema::hasColumn('aceptaciones_criterios', 'fecha_aceptacion')) {
            $this->convertir('aceptaciones_criterios', 'fecha_aceptacion', $driver, true);
        }
    }

    private function convertir(string $tabla, string $columna, string $driver, bool $inverso): void
    {
        if ($driver === 'sqlite') {
            $expresion = $inverso
                ? "datetime(\"{$columna}\", 'utc')"
                : "datetime(\"{$columna}\", 'localtime')";
        } else {
            $desde = $inverso ? '-06:00' : '+00:00';
            $hasta = $inverso ? '+00:00' : '-06:00';
            $expresion = "CONVERT_TZ(`{$columna}`, '{$desde}', '{$hasta}')";
        }

        DB::table($tabla)
            ->whereNotNull($columna)
            ->update([$columna => DB::raw($expresion)]);
    }
};

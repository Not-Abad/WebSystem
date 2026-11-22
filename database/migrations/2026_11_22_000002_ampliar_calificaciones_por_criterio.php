<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El docente captura el porcentaje obtenido (0-100) por cada criterio y
     * se conserva el puntaje de 0-10 que usa el formato REINCO.
     */
    public function up(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->decimal('porcentaje_obtenido', 5, 2)->nullable()->after('puntaje_obtenido');
        });
    }

    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropColumn('porcentaje_obtenido');
        });
    }
};

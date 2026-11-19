<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_calificacion');
            $table->unsignedBigInteger('id_criterio');
            $table->unsignedBigInteger('id_alumno');
            $table->decimal('puntaje_obtenido', 5, 2);
            $table->timestamps();

            $table->unique(['id_criterio', 'id_alumno'], 'uk_calificaciones_criterio_alumno');
            $table->index('id_alumno', 'idx_calificaciones_alumno');

            $table->foreign('id_criterio', 'fk_calificaciones_criterio')
                ->references('id_criterio')->on('criterios_evaluacion')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_alumno', 'fk_calificaciones_alumno')
                ->references('id_alumno')->on('alumnos')
                ->onUpdate('cascade')->onDelete('restrict');
        });
        DB::statement('ALTER TABLE `calificaciones` ADD CONSTRAINT `chk_calificaciones_puntaje` CHECK (`puntaje_obtenido` >= 0.00 AND `puntaje_obtenido` <= 10.00)');
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};

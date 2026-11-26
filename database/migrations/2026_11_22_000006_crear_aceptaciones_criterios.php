<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada alumno acepta una sola vez los criterios publicados por parcial.
     * Se guarda una copia de los criterios aceptados para que la aceptación
     * no cambie si el docente edita la evaluación después.
     */
    public function up(): void
    {
        Schema::create('aceptaciones_criterios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_aceptacion');
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_clase');
            $table->unsignedBigInteger('id_evaluacion');
            $table->json('criterios');
            $table->dateTime('fecha_aceptacion')->useCurrent();
            $table->timestamps();

            $table->unique(
                ['id_alumno', 'id_evaluacion'],
                'uk_aceptaciones_alumno_evaluacion'
            );
            $table->index('id_clase', 'idx_aceptaciones_clase');

            $table->foreign('id_alumno', 'fk_aceptaciones_alumno')
                ->references('id_alumno')->on('alumnos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_clase', 'fk_aceptaciones_clase')
                ->references('id_clase')->on('clases')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_evaluacion', 'fk_aceptaciones_evaluacion')
                ->references('id_evaluacion')->on('evaluaciones')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aceptaciones_criterios');
    }
};

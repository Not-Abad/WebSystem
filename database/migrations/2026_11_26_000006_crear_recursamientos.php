<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de recursamientos (R1 a R4) por alumno y materia.
     * Cada ronda tiene un solo examen y una sola calificación final.
     */
    public function up(): void
    {
        Schema::create('recursamientos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_recursamiento');
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_materia');
            $table->unsignedBigInteger('id_plan');
            $table->unsignedSmallInteger('semestre');
            $table->unsignedBigInteger('id_grupo_original')->nullable();
            $table->unsignedBigInteger('id_docente')->nullable();
            $table->unsignedTinyInteger('ronda');
            $table->string('estatus', 20)->default('pendiente');
            $table->decimal('calificacion_final', 4, 2)->nullable();
            $table->date('fecha_examen')->nullable();
            $table->timestamps();

            $table->unique(['id_alumno', 'id_materia', 'ronda'], 'uk_recursamiento_alumno_materia_ronda');
            $table->index(['id_docente', 'estatus'], 'idx_recursamiento_docente_estatus');
            $table->index(['id_alumno', 'id_materia'], 'idx_recursamiento_alumno_materia');

            $table->foreign('id_alumno', 'fk_recursamiento_alumno')
                ->references('id_alumno')->on('alumnos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_materia', 'fk_recursamiento_materia')
                ->references('id_materia')->on('materias')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_plan', 'fk_recursamiento_plan')
                ->references('id_plan')->on('planes_estudio')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_grupo_original', 'fk_recursamiento_grupo')
                ->references('id_grupo')->on('grupos')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('id_docente', 'fk_recursamiento_docente')
                ->references('id_docente')->on('docentes')->onUpdate('cascade')->onDelete('set null');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `recursamientos` ADD CONSTRAINT `chk_recursamiento_ronda` CHECK (`ronda` BETWEEN 1 AND 4)');
            DB::statement("ALTER TABLE `recursamientos` ADD CONSTRAINT `chk_recursamiento_estatus` CHECK (`estatus` IN ('pendiente', 'aprobada', 'reprobada', 'baja_temporal'))");
            DB::statement('ALTER TABLE `recursamientos` ADD CONSTRAINT `chk_recursamiento_calificacion` CHECK (`calificacion_final` IS NULL OR (`calificacion_final` >= 0.00 AND `calificacion_final` <= 10.00))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recursamientos');
    }
};

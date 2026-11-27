<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial académico persistente: una fila por alumno y materia cursada.
     * No depende del grupo vigente, por lo que sobrevive a la reinscripción.
     */
    public function up(): void
    {
        Schema::create('kardex_materias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_kardex');
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_materia');
            $table->unsignedBigInteger('id_plan');
            $table->unsignedSmallInteger('semestre');
            $table->string('estatus', 20)->default('cursada');
            $table->decimal('calificacion_final', 4, 2)->nullable();
            $table->date('fecha_finalizacion')->nullable();
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->timestamps();

            $table->unique(['id_alumno', 'id_materia'], 'uk_kardex_alumno_materia');
            $table->index(['id_alumno', 'semestre'], 'idx_kardex_alumno_semestre');

            $table->foreign('id_alumno', 'fk_kardex_alumno')
                ->references('id_alumno')->on('alumnos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_materia', 'fk_kardex_materia')
                ->references('id_materia')->on('materias')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_plan', 'fk_kardex_plan')
                ->references('id_plan')->on('planes_estudio')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_grupo', 'fk_kardex_grupo')
                ->references('id_grupo')->on('grupos')
                ->onUpdate('cascade')->onDelete('set null');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `kardex_materias` ADD CONSTRAINT `chk_kardex_estatus` CHECK (`estatus` IN ('cursada'))");
            DB::statement('ALTER TABLE `kardex_materias` ADD CONSTRAINT `chk_kardex_calificacion` CHECK (`calificacion_final` IS NULL OR (`calificacion_final` >= 0.00 AND `calificacion_final` <= 10.00))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex_materias');
    }
};

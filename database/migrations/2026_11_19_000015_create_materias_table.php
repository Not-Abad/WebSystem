<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_materia');
            $table->unsignedBigInteger('id_plan');
            $table->unsignedBigInteger('id_semestre');
            $table->unsignedBigInteger('id_capacitacion')->nullable();
            $table->unsignedBigInteger('id_area_propedeutica')->nullable();
            $table->string('clave', 25)->unique('uk_materias_clave');
            $table->string('nombre', 120);
            $table->string('tipo_materia', 20);
            $table->unsignedTinyInteger('horas_semana');
            $table->text('descripcion')->nullable();
            $table->text('objetivo')->nullable();
            $table->string('programa_pdf_ruta', 255)->nullable();
            $table->boolean('is_active')->default(true);

            $table->index('id_plan', 'idx_materias_plan');
            $table->index('id_semestre', 'idx_materias_semestre');
            $table->index('id_capacitacion', 'idx_materias_capacitacion');
            $table->index('id_area_propedeutica', 'idx_materias_area');

            $table->foreign('id_plan', 'fk_materias_plan')
                ->references('id_plan')->on('planes_estudio')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_semestre', 'fk_materias_semestre')
                ->references('id_semestre')->on('semestres')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_capacitacion', 'fk_materias_capacitacion')
                ->references('id_capacitacion')->on('capacitaciones')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_area_propedeutica', 'fk_materias_area')
                ->references('id_area_propedeutica')->on('areas_propedeuticas')
                ->onUpdate('cascade')->onDelete('restrict');
        });
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `materias` ADD CONSTRAINT `chk_materias_tipo` CHECK (`tipo_materia` IN ('Basica', 'Paraescolar', 'Capacitacion', 'Propedeutica'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique('uk_alumnos_usuario');
            $table->unsignedBigInteger('id_tutor');
            $table->unsignedBigInteger('id_estado_alumno');
            $table->unsignedBigInteger('id_plan');
            $table->unsignedBigInteger('id_semestre');
            $table->unsignedBigInteger('id_capacitacion')->nullable();
            $table->unsignedBigInteger('id_area_propedeutica')->nullable();
            $table->string('matricula', 25)->unique('uk_alumnos_matricula');
            $table->string('nombre', 60);
            $table->string('apellido_paterno', 60);
            $table->string('apellido_materno', 60)->nullable();
            $table->char('curp', 18)->unique('uk_alumnos_curp');
            $table->string('acta_nacimiento_ruta', 255);
            $table->string('boleta_secundaria_ruta', 255);
            $table->string('correo', 120)->unique('uk_alumnos_correo');
            $table->decimal('promedio_general', 4, 2)->default(0.00);
            $table->timestamps();

            $table->index('id_tutor', 'idx_alumnos_tutor');
            $table->index('id_estado_alumno', 'idx_alumnos_estado');
            $table->index('id_plan', 'idx_alumnos_plan');
            $table->index('id_semestre', 'idx_alumnos_semestre');
            $table->index('id_capacitacion', 'idx_alumnos_capacitacion');
            $table->index('id_area_propedeutica', 'idx_alumnos_area');

            $table->foreign('id_usuario', 'fk_alumnos_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_tutor', 'fk_alumnos_tutor')
                ->references('id_tutor')->on('tutores')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_estado_alumno', 'fk_alumnos_estado')
                ->references('id_estado_alumno')->on('estados_alumno')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_plan', 'fk_alumnos_plan')
                ->references('id_plan')->on('planes_estudio')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_semestre', 'fk_alumnos_semestre')
                ->references('id_semestre')->on('semestres')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_capacitacion', 'fk_alumnos_capacitacion')
                ->references('id_capacitacion')->on('capacitaciones')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_area_propedeutica', 'fk_alumnos_area')
                ->references('id_area_propedeutica')->on('areas_propedeuticas')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_docente');
            $table->unsignedBigInteger('id_usuario')->unique('uk_docentes_usuario');
            $table->unsignedBigInteger('id_capacitacion')->nullable();
            $table->unsignedBigInteger('id_area_propedeutica')->nullable();
            $table->string('nombre', 60);
            $table->string('apellido_paterno', 60);
            $table->string('apellido_materno', 60)->nullable();
            $table->string('matricula_rfc', 20)->unique('uk_docentes_matricula');
            $table->string('correo_institucional', 120)->unique('uk_docentes_correo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('id_capacitacion', 'idx_docentes_capacitacion');
            $table->index('id_area_propedeutica', 'idx_docentes_area');
            $table->foreign('id_usuario', 'fk_docentes_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_capacitacion', 'fk_docentes_capacitacion')
                ->references('id_capacitacion')->on('capacitaciones')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_area_propedeutica', 'fk_docentes_area')
                ->references('id_area_propedeutica')->on('areas_propedeuticas')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};

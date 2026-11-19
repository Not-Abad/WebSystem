<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clases', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_clase');
            $table->unsignedBigInteger('id_materia');
            $table->unsignedBigInteger('id_docente');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_periodo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['id_materia', 'id_grupo', 'id_periodo'], 'uk_clases_materia_grupo_periodo');
            $table->index('id_docente', 'idx_clases_docente');
            $table->index('id_grupo', 'idx_clases_grupo');
            $table->index('id_periodo', 'idx_clases_periodo');

            $table->foreign('id_materia', 'fk_clases_materia')
                ->references('id_materia')->on('materias')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_docente', 'fk_clases_docente')
                ->references('id_docente')->on('docentes')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_grupo', 'fk_clases_grupo')
                ->references('id_grupo')->on('grupos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_periodo', 'fk_clases_periodo')
                ->references('id_periodo')->on('periodos_academicos')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clases');
    }
};

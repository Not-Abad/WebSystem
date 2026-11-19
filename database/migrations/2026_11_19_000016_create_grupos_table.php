<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_grupo');
            $table->unsignedBigInteger('id_periodo');
            $table->unsignedBigInteger('id_semestre');
            $table->string('nombre', 15);
            $table->unsignedTinyInteger('cupo_maximo')->default(45);

            $table->unique(['id_periodo', 'nombre'], 'uk_grupos_periodo_nombre');
            $table->index('id_semestre', 'idx_grupos_semestre');

            $table->foreign('id_periodo', 'fk_grupos_periodo')
                ->references('id_periodo')->on('periodos_academicos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_semestre', 'fk_grupos_semestre')
                ->references('id_semestre')->on('semestres')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};

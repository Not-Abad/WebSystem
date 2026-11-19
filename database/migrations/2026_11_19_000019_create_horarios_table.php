<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_horario');
            $table->unsignedBigInteger('id_clase');
            $table->string('dia_semana', 15);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('aula', 30)->nullable();

            $table->index('id_clase', 'idx_horarios_clase');
            $table->index(['dia_semana', 'hora_inicio', 'hora_fin'], 'idx_horarios_dia_horas');

            $table->foreign('id_clase', 'fk_horarios_clase')
                ->references('id_clase')->on('clases')
                ->onUpdate('cascade')->onDelete('cascade');
        });
        DB::statement("ALTER TABLE `horarios` ADD CONSTRAINT `chk_horarios_dia` CHECK (`dia_semana` IN ('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'))");
        DB::statement('ALTER TABLE `horarios` ADD CONSTRAINT `chk_horarios_horas` CHECK (`hora_fin` > `hora_inicio`)');
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};

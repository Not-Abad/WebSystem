<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->unique(
                ['id_clase', 'dia_semana', 'hora_inicio', 'hora_fin'],
                'uk_horarios_clase_dia_hora'
            );
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropUnique('uk_horarios_clase_dia_hora');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clases', function (Blueprint $table) {
            $table->dropUnique('uk_clases_materia_grupo_periodo');
            $table->unique(
                ['id_materia', 'id_docente', 'id_grupo', 'id_periodo'],
                'uk_clases_materia_docente_grupo_periodo'
            );
        });
    }

    public function down(): void
    {
        Schema::table('clases', function (Blueprint $table) {
            $table->dropUnique('uk_clases_materia_docente_grupo_periodo');
            $table->unique(
                ['id_materia', 'id_grupo', 'id_periodo'],
                'uk_clases_materia_grupo_periodo'
            );
        });
    }
};

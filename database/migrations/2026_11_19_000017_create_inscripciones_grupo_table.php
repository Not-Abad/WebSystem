<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones_grupo', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_inscripcion');
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_grupo');
            $table->date('fecha_inscripcion');
            $table->string('estatus', 15)->default('Inscrito');

            $table->unique(['id_alumno', 'id_grupo'], 'uk_inscripciones_alumno_grupo');
            $table->index('id_grupo', 'idx_inscripciones_grupo');

            $table->foreign('id_alumno', 'fk_inscripciones_alumno')
                ->references('id_alumno')->on('alumnos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_grupo', 'fk_inscripciones_grupo')
                ->references('id_grupo')->on('grupos')
                ->onUpdate('cascade')->onDelete('restrict');
        });
        DB::statement("ALTER TABLE `inscripciones_grupo` ADD CONSTRAINT `chk_inscripciones_estatus` CHECK (`estatus` IN ('Inscrito', 'Baja'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones_grupo');
    }
};

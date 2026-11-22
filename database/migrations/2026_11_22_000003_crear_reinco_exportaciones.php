<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reinco_exportaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_exportacion');
            $table->unsignedBigInteger('id_docente');
            $table->unsignedBigInteger('id_clase');
            $table->unsignedBigInteger('id_evaluacion');
            $table->unsignedTinyInteger('parcial');
            $table->string('titulo', 120);
            $table->string('archivo_ruta', 255);
            $table->unsignedInteger('tamano_bytes')->nullable();
            $table->timestamps();

            $table->index('id_docente', 'idx_reinco_docente');

            $table->foreign('id_docente', 'fk_reinco_docente')
                ->references('id_docente')->on('docentes')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_clase', 'fk_reinco_clase')
                ->references('id_clase')->on('clases')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_evaluacion', 'fk_reinco_evaluacion')
                ->references('id_evaluacion')->on('evaluaciones')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reinco_exportaciones');
    }
};

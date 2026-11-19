<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_documento');
            $table->unsignedBigInteger('id_administrador');
            $table->string('titulo', 150);
            $table->string('categoria', 60);
            $table->string('archivo_ruta', 255);
            $table->dateTime('fecha_subida')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('id_administrador', 'idx_documentos_admin');
            $table->foreign('id_administrador', 'fk_documentos_administrador')
                ->references('id_administrador')->on('administradores')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};

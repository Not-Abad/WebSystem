<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noticias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_noticia');
            $table->unsignedBigInteger('id_administrador');
            $table->string('titulo', 160);
            $table->text('contenido');
            $table->string('imagen_portada_ruta', 255)->nullable();
            $table->dateTime('fecha_publicacion')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('id_administrador', 'idx_noticias_admin');
            $table->foreign('id_administrador', 'fk_noticias_administrador')
                ->references('id_administrador')->on('administradores')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};

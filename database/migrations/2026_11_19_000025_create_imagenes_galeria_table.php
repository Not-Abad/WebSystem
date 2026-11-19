<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes_galeria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_imagen');
            $table->unsignedBigInteger('id_administrador');
            $table->string('titulo', 120);
            $table->string('descripcion', 255)->nullable();
            $table->string('imagen_ruta', 255);
            $table->dateTime('fecha_subida')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('id_administrador', 'idx_galeria_admin');
            $table->foreign('id_administrador', 'fk_galeria_administrador')
                ->references('id_administrador')->on('administradores')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes_galeria');
    }
};
